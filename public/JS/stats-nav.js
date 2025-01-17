document.addEventListener('DOMContentLoaded', () => {
    const navButtons = document.querySelectorAll('.stats-icon');
    const detailsSections = document.querySelectorAll('.details-content');

    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all sections
            detailsSections.forEach(section => section.classList.remove('active'));

            // Get the target ID from the button's data attribute
            const targetId = button.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);

            // Add active class to the corresponding section
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });
});
