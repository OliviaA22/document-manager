// documentmanager.js

humhub.module("documentmanager", function (module, require, $) {
  var event = require("event");
  // Your module logic and functionality go here
  event.on("humhub:ready", function () {
    
    $(".documentmanager-folder-updater").click(function (e) {
      e.preventDefault();

      $(".fa-folder").removeClass("fa-folder-open");
      $(this).parent().find(".fa-folder").first().addClass("fa-folder-open");

      var newUrl = $(this).attr("href");
      $("#documentmanager-iframe").attr("src", newUrl);
    });

    $(".search-form").submit(function (e) {
      e.preventDefault();

      var searchUrl = $(this).attr("action") + "?" + $(this).serialize();
      $("#documentmanager-iframe").attr("src", searchUrl);
    });

    const folderfield = document.getElementById("folder-field");
    const newFolderFields = document.getElementById("new-folder-fields");
    const parentFolderField = document.getElementById("parent-folder-field");

    $("#create-new-folder-checkbox").change(function (e) {
      if (this.checked) {
        folderfield.disabled = true;
        newFolderFields.disabled = false;
        parentFolderField.disabled = false;
      } else {
        newFolderFields.disabled = true;
        folderfield.disabled = false;
        parentFolderField.disabled = true;
      }
    });

    $(".collapseClass").click(function () {
      $(this)
        .find(".collapse-icon")
        .toggleClass("fa-chevron-right fa-chevron-down");
    });

    setTimeout(function () {
      $("#flash-message").fadeOut("slow");
    }, 1000);


  });

});
