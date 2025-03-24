// adminload.js
document.addEventListener('DOMContentLoaded', async function() {
    const sidebarContainer = document.getElementById('sidebar-container');
    try {
        const response = await fetch('sidebar.html');
        const sidebarHTML = await response.text();
        sidebarContainer.innerHTML = sidebarHTML;
    } catch (error) {
        console.error("Error loading sidebar:", error);
    }
});