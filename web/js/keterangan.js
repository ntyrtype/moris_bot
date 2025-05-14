// Fungsi untuk membuka modal berdasarkan no_tiket
function openModal(no_tiket) {
    // Set nilai input hidden dengan nomor tiket yang dipilih
    document.getElementById('pickup_tiket').value = no_tiket;

     // Tampilkan modal dengan mengubah style display menjadi block
    document.getElementById('modal').style.display = "block";
}
 // Close the modal
function closeModal() {
    // Sembunyikan modal dengan mengubah style display menjadi none
    document.getElementById('modal').style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    // Jika yang diklik adalah element modal (background gelap)
    if (event.target == document.getElementById('modal')) {
        closeModal(); // Panggil fungsi penutup modal
    }
}