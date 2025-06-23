// Insert privacy link programmatically
document.addEventListener("DOMContentLoaded", function() {
    const footer = document.querySelector("footer");
    if (footer) {
      const privacyLink = document.createElement("a");
      privacyLink.href = "privacy-policy.html";
      privacyLink.style.display = "none";
      privacyLink.textContent = "Privacy Policy";
      footer.appendChild(privacyLink);
    }
  });
