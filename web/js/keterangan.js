function openModal(no_tiket) {
    document.getElementById('pickup_tiket').value = no_tiket;
    document.getElementById('modal').style.display = "block";
}
 // Close the modal
function closeModal() {
    document.getElementById('modal').style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == document.getElementById('modal')) {
        closeModal();
    }
}