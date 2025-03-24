<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';
require_once dirname(__FILE__) . '/../cache/SentimentCache.php';

try {
    // Get current election ID first
    $stmt = $pdo->query("SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1");
    $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentElection) {
        die("Please set a current election first before viewing analytics.");
    }

    // Fetch feedbacks for current election only
    $stmt = $pdo->prepare("SELECT f.*, s.student_name 
                          FROM feedbacks f 
                          JOIN students s ON f.student_id = s.student_id 
                          WHERE f.election_id = :election_id
                          ORDER BY f.feedback_timestamp DESC");
    $stmt->execute(['election_id' => $currentElection['election_id']]);
    
    if ($stmt->rowCount() === 0) {
        $feedbacks = [];
        echo "<script>console.log('No feedbacks found in database');</script>";
    } else {
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Debug output
        echo "<script>
            console.log('Total feedbacks found:', " . count($feedbacks) . ");
            console.log('Feedback data:', " . json_encode($feedbacks) . ");
        </script>";
    }
} catch (PDOException $e) {
    echo "<script>console.error('Database error: " . addslashes($e->getMessage()) . "');</script>";
    $feedbacks = [];
}

// Initialize sentiment cache
$sentimentCache = new SentimentCache();

// Process feedbacks with cache information
try {
    $feedbacksWithCache = array_map(function($feedback) use ($sentimentCache, $currentElection) {
        $cacheKey = md5($feedback['suggestion']); // Create unique key for each feedback
        $cachedSentiment = $sentimentCache->get($cacheKey, $currentElection['election_id']);
        return array_merge($feedback, [
            'cache_key' => $cacheKey,
            'cached_sentiment' => $cachedSentiment
        ]);
    }, $feedbacks);

    $feedbacksJSON = json_encode($feedbacksWithCache, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(json_last_error_msg());
    }
} catch (Exception $e) {
    echo "<script>console.error('JSON encoding error: " . addslashes($e->getMessage()) . "');</script>";
    $feedbacksJSON = '[]';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/toxicity"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .analytics-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 2rem;
            margin-left: 250px; /* Add this line to match sidebar width */
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .chart-card:hover {
            transform: translateY(-5px);
        }
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            text-align: left;
        }
        .stats-summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            min-width: 150px;
        }
        .comments-section {
            margin-top: 2rem;
            padding: 2rem;
        }
        .sentiment-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .sentiment-tab {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .sentiment-tab.active {
            transform: translateY(-2px);
        }
        .comments-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        .comment-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .comment-text {
            font-size: 0.9rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }
        .comment-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
    <script>
        // Define showComments in global scope
        function showComments(sentiment) {
            const containers = document.querySelectorAll('.comments-container');
            const tabs = document.querySelectorAll('.sentiment-tab');
            
            containers.forEach(container => container.style.display = 'none');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            document.getElementById(`${sentiment.toLowerCase()}-comments`).style.display = 'grid';
            document.querySelector(`[data-sentiment="${sentiment}"]`).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', async function () {
            try {
                const feedbacks = <?= $feedbacksJSON ?>;
                console.log('Raw feedbacks data:', feedbacks);
                
                // Add loading indicator
                const mainContent = document.querySelector('.analytics-container');
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'text-center p-4 loading-indicator';
                loadingDiv.innerHTML = 'Loading analytics...';
                mainContent.insertBefore(loadingDiv, mainContent.firstChild);

                // Debug check for data
                if (feedbacks && feedbacks.length > 0) {
                    console.table(feedbacks);
                } else {
                    // Remove loading indicator before showing no data message
                    document.querySelector('.loading-indicator')?.remove();
                    
                    mainContent.insertAdjacentHTML('afterbegin', `
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                            <p class="font-bold">No Feedback Data</p>
                            <p>There are currently no feedbacks in the database.</p>
                        </div>
                    `);
                    return;
                }

                const apiKey = '1d27e7bc01cf6e76d42e2115883bfbae'; // MeaningCloud API key
                // backup key 'a777cea33da118e4703a82dddefab1a4'

                // Function to handle API errors
                async function handleApiResponse(response) {
                    if (!response.ok) {
                        throw new Error(`API request failed: ${response.status}`);
                    }
                    const data = await response.json();
                    if (data.status.code !== '0') {
                        throw new Error(`MeaningCloud API error: ${data.status.msg}`);
                    }
                    return data;
                }

                // Add rate limiting and caching
                const sentimentCache = new Map();
                const RATE_LIMIT_DELAY = 1000; // 1 second delay between API calls

                async function getSentiment(text, cacheKey, cachedSentiment) {
                    try {
                        // If we have cached sentiment data from PHP, use it
                        if (cachedSentiment) {
                            console.log('Using PHP cached sentiment for:', text.substring(0, 50) + '...');
                            return cachedSentiment;
                        }

                        // Check memory cache
                        if (sentimentCache.has(cacheKey)) {
                            console.log('Using memory-cached sentiment for:', text.substring(0, 50) + '...');
                            return sentimentCache.get(cacheKey);
                        }

                        // Pre-process text for enthusiasm check
                        const isEnthusiastic = text.includes('!') && 
                            (text.toUpperCase() === text || 
                             text.includes('BEST') || 
                             text.includes('GREAT') || 
                             text.includes('AMAZING') ||
                             text.includes('EXCELLENT'));

                        if (!text || text.trim().length === 0) {
                            return {
                                score_tag: 'NEU',
                                confidence: 0,
                                agreement: 'DISAGREEMENT',
                                subjectivity: 'OBJECTIVE'
                            };
                        }

                        // Add delay for rate limiting
                        await new Promise(resolve => setTimeout(resolve, RATE_LIMIT_DELAY));

                        const params = new URLSearchParams();
                        params.append('key', apiKey);
                        params.append('txt', text);
                        params.append('lang', 'en');

                        const response = await fetch('https://api.meaningcloud.com/sentiment-2.1', {
                            method: 'POST',
                            body: params
                        });

                        const data = await response.json();
                        
                        if (data.status.code !== '0') {
                            if (data.status.code === '104') {
                                console.warn('Rate limit hit, using neutral sentiment');
                                return {
                                    score_tag: 'NEU',
                                    confidence: 50,
                                    agreement: 'AGREEMENT',
                                    subjectivity: 'OBJECTIVE'
                                };
                            }
                            throw new Error(`MeaningCloud API error: ${data.status.msg}`);
                        }

                        // Override sentiment for enthusiastic feedback
                        if (isEnthusiastic && data.score_tag === 'NEU') {
                            console.log('Overriding neutral sentiment for enthusiastic feedback:', text);
                            data.score_tag = 'P+';
                            data.confidence = Math.max(data.confidence, 90);
                        }

                        const result = {
                            score_tag: data.score_tag,
                            confidence: parseInt(data.confidence),
                            agreement: data.agreement,
                            subjectivity: data.subjectivity
                        };

                        // Cache the result both in memory and server
                        sentimentCache.set(cacheKey, result);
                        
                        // Save to server cache using the SentimentCache PHP class
                        const currentElectionId = <?php echo $currentElection['election_id']; ?>;
                        await fetch('../cache/save_sentiment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                key: cacheKey,
                                value: result,
                                election_id: currentElectionId
                            })
                        });

                        return result;
                    } catch (error) {
                        console.error('Sentiment analysis error:', error);
                        return {
                            score_tag: 'NEU',
                            confidence: 0,
                            agreement: 'DISAGREEMENT',
                            subjectivity: 'OBJECTIVE'
                        };
                    }
                }

                // Modify the feedback processing loop to handle batches
                async function processFeedbackBatch(feedbacks, startIndex, batchSize) {
                    const endIndex = Math.min(startIndex + batchSize, feedbacks.length);
                    for (let i = startIndex; i < endIndex; i++) {
                        const feedback = feedbacks[i];
                        try {
                            console.log(`Processing feedback ${i + 1}/${feedbacks.length}`);
                            const sentimentData = await getSentiment(feedback.suggestion, feedback.cache_key, feedback.cached_sentiment);
                            // ... rest of the feedback processing code ...
                        } catch (error) {
                            console.error('Error processing feedback:', feedback, error);
                            continue;
                        }
                    }
                }

                // Update the main processing loop
                document.addEventListener('DOMContentLoaded', async function () {
                    try {
                        // ...existing initialization code...

                        // Process feedbacks in batches
                        const BATCH_SIZE = 5;
                        for (let i = 0; i < feedbacks.length; i += BATCH_SIZE) {
                            await processFeedbackBatch(feedbacks, i, BATCH_SIZE);
                        }

                        // ...rest of existing code...
                    } catch (error) {
                        // ...existing error handling...
                    }
                });

                const sentimentCounts = { Positive: 0, Neutral: 0, Negative: 0 };
                const experienceCounts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
                const experienceSentiments = {
                    1: { Positive: 0, Neutral: 0, Negative: 0 },
                    2: { Positive: 0, Neutral: 0, Negative: 0 },
                    3: { Positive: 0, Neutral: 0, Negative: 0 },
                    4: { Positive: 0, Neutral: 0, Negative: 0 },
                    5: { Positive: 0, Neutral: 0, Negative: 0 }
                };
                const commentsBySentiment = {
                    Positive: [],
                    Neutral: [],
                    Negative: []
                };

                // Single pass analysis for both charts and comments
                let processedCount = 0;
                const totalFeedbacks = feedbacks.length;

                // Initialize mismatch tracking
                const mismatches = {
                    lowRatingPositiveFeedback: [],
                    highRatingNegativeFeedback: []
                };

                for (const feedback of feedbacks) {
                    try {
                        processedCount++;
                        console.log(`Processing feedback ${processedCount}/${totalFeedbacks}`);
                        
                        const sentimentData = await getSentiment(
                            feedback.suggestion,
                            feedback.cache_key,
                            feedback.cached_sentiment
                        );
                        const experience = parseInt(feedback.experience);
                        experienceCounts[experience]++;

                        // Determine sentiment category with confidence threshold
                        let sentimentCategory;
                        if (sentimentData.confidence >= 70) { // Only consider high confidence results
                            if (sentimentData.score_tag === 'P+' || sentimentData.score_tag === 'P') {
                                sentimentCategory = 'Positive';
                                // Check for low rating with positive sentiment
                                if (experience <= 2) {
                                    mismatches.lowRatingPositiveFeedback.push({
                                        text: feedback.suggestion,
                                        rating: experience,
                                        timestamp: feedback.feedback_timestamp
                                    });
                                }
                            } else if (sentimentData.score_tag === 'N' || sentimentData.score_tag === 'N+') {
                                sentimentCategory = 'Negative';
                                // Check for high rating with negative sentiment
                                if (experience >= 4) {
                                    mismatches.highRatingNegativeFeedback.push({
                                        text: feedback.suggestion,
                                        rating: experience,
                                        timestamp: feedback.feedback_timestamp
                                    });
                                }
                            } else {
                                sentimentCategory = 'Neutral';
                            }
                        } else {
                            sentimentCategory = 'Neutral';
                        }

                        sentimentCounts[sentimentCategory]++;
                        experienceSentiments[experience][sentimentCategory]++;
                        commentsBySentiment[sentimentCategory].push({
                            text: feedback.suggestion,
                            timestamp: feedback.feedback_timestamp,
                            rating: feedback.experience
                        });
                    } catch (error) {
                        console.error('Error processing feedback:', feedback);
                        console.error('Error details:', error);
                        // Continue with next feedback instead of stopping completely
                        continue;
                    }
                }

                // Add new charts and visualizations
                // Update the experience chart with color-coded ratings
                const experienceCtx = document.getElementById('experienceChart').getContext('2d');
                new Chart(experienceCtx, {
                    type: 'bar',
                    data: {
                        labels: ['1', '2', '3', '4', '5'],
                        datasets: [{
                            label: 'Experience Ratings Distribution',
                            data: [
                                experienceCounts[1],
                                experienceCounts[2],
                                experienceCounts[3],
                                experienceCounts[4],
                                experienceCounts[5]
                            ],
                            backgroundColor: [
                                '#ef4444', // Red for rating 1
                                '#f97316', // Orange for rating 2
                                '#facc15', // Yellow for rating 3
                                '#84cc16', // Light green for rating 4
                                '#22c55e', // Green for rating 5
                            ],
                            borderWidth: 1,
                            borderColor: [
                                '#dc2626',
                                '#ea580c',
                                '#eab308',
                                '#65a30d',
                                '#16a34a'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false // Hide legend since colors are self-explanatory
                            },
                            title: {
                                display: true,
                                text: 'Experience Ratings Distribution'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const rating = context.label;
                                        const count = context.raw;
                                        const descriptions = {
                                            '1': 'Very Poor',
                                            '2': 'Poor',
                                            '3': 'Average',
                                            '4': 'Good',
                                            '5': 'Excellent'
                                        };
                                        return `Rating ${rating} (${descriptions[rating]}): ${count} responses`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { 
                                title: { 
                                    display: true, 
                                    text: 'Rating' 
                                }
                            },
                            y: { 
                                beginAtZero: true,
                                title: { 
                                    display: true, 
                                    text: 'Number of Responses' 
                                }
                            }
                        }
                    }
                });

                // Replace the correlation chart with feedback length analysis
                const feedbackLengths = feedbacks.map(f => ({
                    length: f.suggestion.length,
                    rating: parseInt(f.experience)
                }));

                // Group feedback lengths by rating
                const lengthsByRating = {1: [], 2: [], 3: [], 4: [], 5: []};
                feedbackLengths.forEach(f => {
                    lengthsByRating[f.rating].push(f.length);
                });

                // Calculate average lengths for each rating
                const avgLengths = Object.entries(lengthsByRating).map(([rating, lengths]) => ({
                    rating,
                    avgLength: lengths.length ? lengths.reduce((a, b) => a + b, 0) / lengths.length : 0
                }));

                // Create the feedback length chart
                const lengthCtx = document.getElementById('feedbackLengthChart').getContext('2d');
                new Chart(lengthCtx, {
                    type: 'line',
                    data: {
                        labels: ['1', '2', '3', '4', '5'],
                        datasets: [{
                            label: 'Average Feedback Length',
                            data: avgLengths.map(d => d.avgLength),
                            borderColor: '#9333ea',
                            backgroundColor: 'rgba(147, 51, 234, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Average Feedback Length by Rating'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Average Length: ${Math.round(context.raw)} characters`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Characters'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Rating'
                                }
                            }
                        }
                    }
                });

                // Render Bar Chart
                const ctx = document.getElementById('sentimentChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(sentimentCounts),
                        datasets: [{
                            label: 'Number of Comments',
                            data: Object.values(sentimentCounts),
                            backgroundColor: ['#4caf50', '#ffce56', '#f44336'],
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });

                // Prepare data for feedback trends
                const feedbackDates = feedbacks.map(f => f.feedback_timestamp.split(" ")[0]); // Extract dates
                const trendData = feedbackDates.reduce((acc, date) => {
                    acc[date] = (acc[date] || 0) + 1;
                    return acc;
                }, {});

                const sortedDates = Object.keys(trendData).sort();
                const trendValues = sortedDates.map(date => trendData[date]);

                // Render Line Chart
                const lineCtx = document.getElementById('trendChart').getContext('2d');
                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: sortedDates,
                        datasets: [{
                            label: 'Feedback Over Time',
                            data: trendValues,
                            borderColor: '#36A2EB',
                            fill: false,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Feedback Trend Over Time'
                            }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Date' } },
                            y: { title: { display: true, text: 'Number of Feedbacks' }, beginAtZero: true }
                        }
                    }
                });

                // Create Sentiment Distribution Pie Chart
                const pieCtx = document.getElementById('sentimentPieChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Positive', 'Neutral', 'Negative'],
                        datasets: [{
                            data: [
                                sentimentCounts.Positive,
                                sentimentCounts.Neutral,
                                sentimentCounts.Negative
                            ],
                            backgroundColor: ['#4caf50', '#ffce56', '#f44336'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Sentiment Distribution'
                            }
                        },
                        cutout: '50%'
                    }
                });

                // Create Weekly Activity Chart
                // Group feedback by day of week
                const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const weeklyData = new Array(7).fill(0);
                
                feedbacks.forEach(feedback => {
                    const date = new Date(feedback.feedback_timestamp);
                    const dayOfWeek = date.getDay();
                    weeklyData[dayOfWeek]++;
                });

                const weeklyCtx = document.getElementById('weeklyActivityChart').getContext('2d');
                new Chart(weeklyCtx, {
                    type: 'radar',
                    data: {
                        labels: daysOfWeek,
                        datasets: [{
                            label: 'Number of Feedbacks',
                            data: weeklyData,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(54, 162, 235, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Feedback Activity by Day of Week'
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });

                // Update statistics summary
                document.getElementById('totalFeedbacks').textContent = feedbacks.length;
                const avgRating = (Object.entries(experienceCounts).reduce((acc, [key, value]) => 
                    acc + (key * value), 0) / feedbacks.length).toFixed(1);
                document.getElementById('avgRating').textContent = avgRating;
                const sentimentScore = ((sentimentCounts.Positive * 100) / 
                    (sentimentCounts.Positive + sentimentCounts.Neutral + sentimentCounts.Negative)).toFixed(0) + '%';
                document.getElementById('sentimentScore').textContent = sentimentScore;

                // Update the comments display section
                const commentsSection = document.querySelector('.comments-section');
                if (commentsSection) {
                    Object.keys(commentsBySentiment).forEach(sentiment => {
                        const comments = commentsBySentiment[sentiment];
                        const container = document.getElementById(`${sentiment.toLowerCase()}-comments`);
                        const count = comments.length;
                        
                        // Add count to tab
                        const tab = document.querySelector(`[data-sentiment="${sentiment}"]`);
                        tab.innerHTML = `${sentiment} Comments (${count})`;
                        
                        container.innerHTML = ''; // Clear existing content
                        comments.forEach(comment => {
                            container.innerHTML += `
                                <div class="comment-card">
                                    <p class="comment-text">${comment.text}</p>
                                    <div class="comment-meta">
                                        Rating: ${comment.rating}/5 • ${new Date(comment.timestamp).toLocaleDateString()}
                                    </div>
                                </div>
                            `;
                        });
                    });
                    
                    // Call showComments after all tabs are initialized
                    setTimeout(() => showComments('Positive'), 100);
                }

                // Remove loading indicator at the end of processing
                document.querySelector('.loading-indicator')?.remove();

                // Add mismatch chart after other charts
                const mismatchCtx = document.getElementById('mismatchChart').getContext('2d');
                new Chart(mismatchCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Low Rating + Positive Feedback', 'High Rating + Negative Feedback'],
                        datasets: [{
                            label: 'Number of Mismatches',
                            data: [
                                mismatches.lowRatingPositiveFeedback.length,
                                mismatches.highRatingNegativeFeedback.length
                            ],
                            backgroundColor: ['#f59e0b', '#8b5cf6']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Rating-Sentiment Mismatches'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.raw} mismatches found`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });

                // Display mismatched feedbacks
                const positiveMismatchContainer = document.getElementById('positive-mismatch');
                const negativeMismatchContainer = document.getElementById('negative-mismatch');

                if (mismatches.lowRatingPositiveFeedback.length === 0) {
                    positiveMismatchContainer.innerHTML = '<p class="text-gray-500 italic">No mismatches found</p>';
                } else {
                    mismatches.lowRatingPositiveFeedback.forEach(feedback => {
                        positiveMismatchContainer.innerHTML += `
                            <div class="border-l-4 border-yellow-500 pl-3 mb-3">
                                <p class="text-sm text-gray-600">${feedback.text}</p>
                                <div class="text-xs text-gray-500 mt-1">
                                    Rating: ${feedback.rating}/5 • ${new Date(feedback.timestamp).toLocaleDateString()}
                                </div>
                            </div>
                        `;
                    });
                }

                if (mismatches.highRatingNegativeFeedback.length === 0) {
                    negativeMismatchContainer.innerHTML = '<p class="text-gray-500 italic">No mismatches found</p>';
                } else {
                    mismatches.highRatingNegativeFeedback.forEach(feedback => {
                        negativeMismatchContainer.innerHTML += `
                            <div class="border-l-4 border-purple-500 pl-3 mb-3">
                                <p class="text-sm text-gray-600">${feedback.text}</p>
                                <div class="text-xs text-gray-500 mt-1">
                                    Rating: ${feedback.rating}/5 • ${new Date(feedback.timestamp).toLocaleDateString()}
                                </div>
                            </div>
                        `;
                    });
                }

            } catch (error) {
                // Remove loading indicator before showing error
                document.querySelector('.loading-indicator')?.remove();
                
                console.error("Error:", error);
                console.error('Detailed error:', error);
                console.error('Stack trace:', error.stack);
                alert("An error occurred while loading analytics.");
                
                // Show error in UI
                const mainContent = document.querySelector('.analytics-container');
                mainContent.innerHTML = `
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Error Loading Analytics</p>
                        <p>${error.message}</p>
                        <button onclick="location.reload()" class="mt-2 bg-red-500 text-white px-4 py-2 rounded">
                            Retry
                        </button>
                    </div>
                ` + mainContent.innerHTML;
            }
        });

    </script>
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>
    
    <main class="analytics-container">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Analytics Dashboard</h1>
        
        <div class="stats-summary">
            <div class="stat-card">
                <h3 class="text-lg">Total Feedbacks</h3>
                <p class="text-2xl font-bold" id="totalFeedbacks">0</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <h3 class="text-lg">Average Rating</h3>
                <p class="text-2xl font-bold" id="avgRating">0</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <h3 class="text-lg">Sentiment Score</h3>
                <p class="text-2xl font-bold" id="sentimentScore">0</p>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <div class="chart-title">Experience Ratings Distribution</div>
                <canvas id="experienceChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Sentiment Analysis</div>
                <canvas id="sentimentChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Feedback Length vs Rating</div>
                <canvas id="feedbackLengthChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Feedback Trends</div>
                <canvas id="trendChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Rating Distribution by Sentiment</div>
                <canvas id="sentimentPieChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Weekly Feedback Activity</div>
                <canvas id="weeklyActivityChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Rating-Sentiment Mismatch Analysis</div>
                <canvas id="mismatchChart"></canvas>
            </div>
        </div>

        <div class="comments-section">
            <h2 class="text-2xl font-bold mb-4">Feedback Comments Analysis</h2>
            
            <div class="sentiment-tabs">
                <button class="sentiment-tab" data-sentiment="Positive" 
                        onclick="showComments('Positive')"
                        style="background: linear-gradient(135deg, #4caf50, #45a049); color: white;">
                    Positive Comments
                </button>
                <button class="sentiment-tab" data-sentiment="Neutral"
                        onclick="showComments('Neutral')"
                        style="background: linear-gradient(135deg, #ffce56, #ffc107); color: white;">
                    Neutral Comments
                </button>
                <button class="sentiment-tab" data-sentiment="Negative"
                        onclick="showComments('Negative')"
                        style="background: linear-gradient(135deg, #f44336, #e53935); color: white;">
                    Negative Comments
                </button>
            </div>

            <div id="positive-comments" class="comments-container" style="display: none;"></div>
            <div id="neutral-comments" class="comments-container" style="display: none;"></div>
            <div id="negative-comments" class="comments-container" style="display: none;"></div>

            <!-- Add new section for mismatched feedback -->
            <div class="mt-8">
                <h2 class="text-2xl font-bold mb-4">Rating-Sentiment Mismatches</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-2 text-red-600">Low Rating, Positive Feedback</h3>
                        <div id="positive-mismatch" class="space-y-2"></div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-2 text-green-600">High Rating, Negative Feedback</h3>
                        <div id="negative-mismatch" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
                        <div id="negative-mismatch" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>