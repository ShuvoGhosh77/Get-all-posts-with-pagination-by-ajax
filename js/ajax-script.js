jQuery(document).ready(function ($) {
  let page = 1; // Track the current page
  let currentCategory = ""; // Track the selected category (empty means "All")

  // Function to load posts based on the current page and category
  function loadPosts(category = "", page = 1) {
    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "load_more_posts",
        page: page,
        category: category,
      },
      beforeSend: function () {
        $("#post-container").html('<div class="loading"></div>');
      },
      success: function (response) {
        // Replace the content of the post container
        $("#post-container").html(response);

        // Re-attach click events for pagination
        $(".pagination .page-link").on("click", function (e) {
          e.preventDefault();

          // Get the clicked page number from the link
          let clickedPage = $(this).text();
          page = parseInt(clickedPage);

          loadPosts(currentCategory, page); // Load posts for the selected page
        });
      },
      complete: function () {
        $(".loading").remove(); // Remove loading indicator
      },
    });
  }

  // Initial load (load posts for "All" category)
  loadPosts();

  // When a category filter is clicked
  $(".category-filter").on("click", function (e) {
    e.preventDefault();
    $(".category-filter").removeClass("nav-active");
    $(this).addClass("nav-active");
    page = 1; // Reset page to 1 when a category is clicked
    currentCategory = $(this).data("category"); // Get selected category
    loadPosts(currentCategory, page); // Load posts for the selected category
  });
});
