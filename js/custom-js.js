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

    // Live feedback on the register page while the user types their password
    initPasswordValidation();
});

function initPasswordValidation() {
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const rulesList = document.getElementById("password-rules");
    const matchMessage = document.getElementById("password-match");

    // Only present on register.php
    if (!password || !rulesList) return;

    const rules = {
        length: (value) => value.length >= 8,
        upper:  (value) => /[A-Z]/.test(value),
        lower:  (value) => /[a-z]/.test(value),
        digit:  (value) => /\d/.test(value),
        symbol: (value) => /[^A-Za-z0-9]/.test(value),
    };

    password.addEventListener("input", () => {
        const value = password.value;
        Object.entries(rules).forEach(([rule, test]) => {
            const item = rulesList.querySelector(`[data-rule="${rule}"]`);
            if (item) item.classList.toggle("rule-met", test(value));
        });
        updatePasswordMatch();
    });

    if (confirmPassword) {
        confirmPassword.addEventListener("input", updatePasswordMatch);
    }

    function updatePasswordMatch() {
        if (!confirmPassword || !matchMessage) return;
        if (!confirmPassword.value) {
            matchMessage.textContent = "";
            return;
        }
        const matches = password.value === confirmPassword.value;
        matchMessage.textContent = matches ? matchMessage.dataset.matchText : matchMessage.dataset.mismatchText;
        matchMessage.classList.toggle("match-ok", matches);
        matchMessage.classList.toggle("match-fail", !matches);
    }
}

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