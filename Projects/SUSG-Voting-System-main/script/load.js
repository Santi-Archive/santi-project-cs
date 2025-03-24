// Fetch and inject the header
fetch('header.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('header').innerHTML = html;
        console.log("Header loaded successfully");

        // Attach event listeners for the hamburger menu after loading the header
        const menuToggle = document.getElementById('header-menu-toggle');
        const sideMenu = document.getElementById('header-side-menu');
        const overlay = document.getElementById('header-overlay');

        if (menuToggle && sideMenu && overlay) {
            menuToggle.addEventListener("click", () => {
                sideMenu.classList.toggle("active");
                overlay.classList.toggle("active");
                document.body.classList.toggle("header-overlay-active");
            });

            overlay.addEventListener("click", () => {
                sideMenu.classList.remove("active");
                overlay.classList.remove("active");
                document.body.classList.remove("header-overlay-active");
            });
        }
    })
    .catch(error => console.error('Error loading the header:', error));

// Fetch and inject the footer
fetch('footer.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('footer').innerHTML = html;
        console.log('Footer loaded successfully');
    })
    .catch(error => console.error('Error loading the footer:', error));