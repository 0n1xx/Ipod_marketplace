// Waiting until the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Identifying a button which is called burger
    const burger = document.querySelector(".burger");
    const nav = document.querySelector("header nav");

    // When the button is clicked, making the menu be visible to our users
    burger.addEventListener("click", () => {
        burger.classList.toggle("active");
        nav.classList.toggle("open");
    });
});