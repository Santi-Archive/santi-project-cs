// Fetch and inject the login page header
fetch('loginpageheader.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('header').innerHTML = html;
        console.log("Login page header loaded successfully");

        // Example: Attach event listener after header loads, if needed
        const userLogo = document.getElementById("user-logo");
        if (userLogo) {
            userLogo.onclick = function() {
                const submenu = document.getElementById("subMenu");
                submenu.classList.toggle('open-menu');
            };
        }
    })
    .catch(error => console.error('Error loading the login page header:', error));

// Fetch and inject the footer
fetch('footer.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('footer').innerHTML = html;
        console.log('Footer loaded successfully');
    })
    .catch(error => {
        console.error('Error loading the footer:', error);
    });
