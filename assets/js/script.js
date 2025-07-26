// JavaScript for interactivity (expand/collapse rows, button actions)
document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('.expandable-row');
    rows.forEach(row => {
        row.addEventListener('click', () => {
            row.classList.toggle('expanded');
        });
    });

    const rentButtons = document.querySelectorAll('.rent-button');
    rentButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Logic to move car to "rented" table
        });
    });

    const releaseButtons = document.querySelectorAll('.release-button');
    releaseButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Logic to move car back to "available" table
        });
    });
});

