</div> <!-- ./padding -->
<div class="row text-center justify-content-md-center">
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/08/sf1-300x227.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/08/op1-300x162.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/08/glpf1-297x300.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/07/ob-300x300.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/07/epa-300x300.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/07/glca-300x300.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/08/lucid-300x252.jpg" alt="" class="img-fluid grow">
  </div>
  <div class="col-3 col-md-1">
    <img src="https://environmentaldashboard.org/images/uploads/2015/07/oc-300x300.jpg" alt="" class="img-fluid grow">
  </div>
</div>
<div class="row">
  <div class="col"><p class="text-muted text-center" style="margin-top: 30px">Oberlin College <?php echo date('Y') ?> | <a href="mailto:dashboard@oberlin.edu" style="color: #6c757d; text-decoration: underline;">Contact Us</a></p></div>
</div>
<div class="row">
  <div class="col text-center" style="padding-bottom: 50px">
    <a href="https://www.facebook.com/oberlindashboard/" class="btn btn-primary" style="height: 35px;width: 35px;padding: 5px"><img src="https://environmentaldashboard.org/images/facebook-f.svg" alt="Environmental Dashboard on Facebook" style="height: 100%"></a>
    <a href="https://twitter.com/envirodashboard" class="btn btn-primary" style="height: 35px;width: 35px;padding: 5px"><img src="https://environmentaldashboard.org/images/twitter.svg" style="height: 100%" alt="Environmental Dashboard on Twitter"></a>
    <a href="https://www.instagram.com/environmentaldashboard_project/" class="btn btn-primary" style="height: 35px;width: 35px;padding: 5px"><img src="https://environmentaldashboard.org/images/instagram.svg" style="height: 100%" alt="Environmental Dashboard on Instagram"></a>
  </div>
</div>
</div> <!-- /.container -->
    <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
<!-- only the submit form on the index page needs the full jquery -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script>
$(document).ready(function() {
	// from https://github.com/bootstrapthemesco/bootstrap-4-multi-dropdown-navbar
  $('.dropdown-menu a.dropdown-toggle').on('click', function (e) {
    var $el = $(this);
    var $parent = $(this).offsetParent(".dropdown-menu");
    if (!$(this).next().hasClass('show')) {
      $(this).parents('.dropdown-menu').first().find( '.show').removeClass("show");
    }
    var $subMenu = $(this).next(".dropdown-menu");
    $subMenu.toggleClass('show');
    $(this).parent("li").toggleClass('show');
    $(this).parents('li.nav-item.dropdown.show' ).on( 'hidden.bs.dropdown', function (e) {
      $('.dropdown-menu .show').removeClass("show");
    });
    if ( !$parent.parent().hasClass('navbar-nav')) {
      $el.next().css( { "top": $el[0].offsetTop, "left": -$subMenu.outerWidth() } );
    }
    return false;
  });
});
</script>
    <script>
      $('#eventModal').on('show.bs.modal', function (event) {
      	var dest = $('#modal-body');
      	dest.html('');
			  var button = $(event.relatedTarget);
			  $('#eventModalLabel').text('Events on '+button.data('date'));
			  var ids = button.data('ids');
			  var titles = JSON.parse(button.attr('data-titles'));
			  var descripts = JSON.parse(button.attr('data-descripts'));
			  for (var i=0; i < ids.length; i++) { 
			  	dest.append('<h4>'+titles[i]+'</h4>'+'<p>'+descripts[i]+'</p><p><a class="btn btn-primary btn-sm" href="<?php echo "{$router->base_url}/calendar/detail{$router->detail_page_sep}"; ?>'+ids[i]+'">View more</a></p>');
			  }
			})
    </script>
  </body>
</html>