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

    // Turning the server-rendered .alert_success/.alert_failure blocks
    // (hidden via CSS) into auto-dismissing toast notifications
    initToasts();
});

function initToasts() {
    const sources = document.querySelectorAll(".alert_success, .alert_failure");
    if (!sources.length) return;

    const container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);

    sources.forEach((source) => {
        const type = source.classList.contains("alert_success") ? "success" : "error";
        source.querySelectorAll("p").forEach((p) => {
            showToast(p.textContent, type, container);
        });
    });
}

function showToast(message, type, container) {
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);

    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        toast.classList.add("toast-hide");
        toast.addEventListener("animationend", () => toast.remove());
    }, 4000);
}