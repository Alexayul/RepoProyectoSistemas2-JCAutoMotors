// Archivo: script.js

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search");
    const rows = document.querySelectorAll("#data-table tbody tr");
    
    searchInput.addEventListener("input", function () {
        const searchText = this.value.toLowerCase();
        rows.forEach(row => {
            const rowData = row.innerText.toLowerCase();
            row.style.display = rowData.includes(searchText) ? "" : "none";
        });
    });
});
