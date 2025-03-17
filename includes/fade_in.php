<?php ob_start(); ?> 
<style> 
    /* Fade-in effect */
    body {
        opacity: 0;
        transition: opacity 1s ease-in;
    }
    body.fade-in {
        opacity: 1;
    }
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(() => {
        document.body.classList.add("fade-in"); // Smooth fade-in on page load
    }, 10); // Small delay ensures consistency
});
</script>
<?php ob_end_flush(); ?> 