// JavaScript Document
/* Brower Close Event START */
window.addEventListener("beforeunload", function (e) {
  var x = "<?php logoutuser($_SESSION['UserId']); ?>";
});

