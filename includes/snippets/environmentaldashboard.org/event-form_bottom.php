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
  <script src="js/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
  <script src="js/jquery.timepicker.min.js"></script>
  <script>
    <?php if ($edit) { ?>
    $('#preview').on('click', function(e) {
      e.preventDefault();
      window.open('slide?id=<?php echo $event['id'] ?>', '_blank');
    });
    <?php } ?>
    $('#show-datetime2').on('click', function(e) {
      e.preventDefault();
      $('#datetime2').css('display', '');
      $(this).css('display', 'none');
    });
    $('#other-checkbox').on('change', function() {
      if (this.checked) {
        $('#reg-locs').find('input').prop('checked', true);
      } else {
        $('#reg-locs').find('input').prop('checked', false);
      }
    });
    $('#school-checkbox').on('change', function() {
      if (this.checked) {
        $('#school-locs').find('input').prop('checked', true);
      } else {
        $('#school-locs').find('input').prop('checked', false);
      }
    });
    var sponsor_fields = [<?php for ($i=1; $i < $num_sponsors; $i++) {
      if ($i !== $num_sponsors-1) {
        echo "\$('#sponsor{$i}'), ";
      } else {
        echo "\$('#sponsor{$i}')";
      }
    } ?>],//[$('#sponsor')],
        num_sponsors = <?php echo $num_sponsors; ?>;
    $('#add-another-sponsor').on('click', function(e) {
      e.preventDefault();
      $('#more-sponsors').append('<input type="text" class="form-control" id="sponsor'+num_sponsors+'" data-sponsor="'+num_sponsors+'" name="sponsors[]" value="" maxlength="80" style="margin-top:10px"><div id="invalid-feedback'+num_sponsors+'" class="invalid-feedback"></div><p><a href="#" class="remove-sponsor" style="float:right" data-remove="#sponsor'+num_sponsors+'">Remove</a></p>');
      sponsor_fields.push($('#sponsor'+num_sponsors));
      num_sponsors++;
      init_sponsor_fields();
    });
    $(document).on('click', "a.remove-sponsor", function(e) { // https://stackoverflow.com/a/16893057/2624391
      e.preventDefault();
      $($(this).data('remove')).remove();
      $(this).remove();
    });
    (function($) { // https://stackoverflow.com/a/12426630/2624391
    $.fn.serializefiles = function() {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input[type='file']"), function(i, tag) {
            $.each($(tag)[0].files, function(i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        $.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });
        return formData;
    };
    })(jQuery);

    $('.alert > button').on('click', function() {
      $('.alert').css('display', 'none');
    });

    $('#event-form').on('submit', function(e) {
      e.preventDefault();
      var form_files = $(this);
      if ($('#img').val() !== '') {
        var file = ($('#img'))[0].files[0];
        if (file.size > 16000000) { // 16MB
          $('#alert-warning').css('display', 'block');
          $('#alert-warning-text').text('The image you selected is too large; please upload an image no larger than 16MB');
          return;
        }
        var img = new Image();
        img.src = window.URL.createObjectURL(file);
        img.onload = function() {
          var width = img.naturalWidth,
              height = img.naturalHeight;
          window.URL.revokeObjectURL( img.src );
          if ((height/width) < 1.5) {
            send_data(form_files);
          } else {
            $('#alert-warning').css('display', 'block');
            $('#alert-warning-text').text('The image you selected is too tall; please upload an image with a height no greater than 1.5x the width of the image');
          }
        }
      } else {
        send_data(form_files);
      }
    });

    function send_data(files) {
      var valid_sponsors = true;
      sponsor_fields.forEach(function(f) {
        if (f.hasClass('is-invalid')) {
          valid_sponsors = false;
        }
      });
      var description_len = $('#description').val().length;
      if (description_len < 10 || description_len > 200) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event description must be between 10 and 200 characters.');
      } else if ($('#event').val().length > 60) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event title must be less than 60 characters');
      } else if ($('#event').val().length == 0) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event title is empty');
      } else if ($('#date').val().length < 9) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Invalid start date');
      } else if ($('#date2').val().length < 9) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Invalid end date');
      } else if ($('#event_type').val() == '') {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Please select an event type');
      } else if ($('#loc_id').hasClass('is-invalid')) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Please select a valid location');
      } else if (!valid_sponsors) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Please select a valid sponsor');
      } else {
        $('#submit-btn').val('Loading');
        $('#alert-success').css('display', 'block');
        $('#alert-success-text').text('Loading');
        var data = files.serializefiles();
        $.ajax({
          url: 'includes/<?php echo ($edit) ? 'edit-event' : 'add-event' ?>.php',
          cache: false,
          method: 'POST',
          data: data,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function(resp) {
            console.log(resp);
            if (!isNaN(resp)) { // if valid int
              <?php if ($edit) { ?>
                $('#alert-success-text').text('Your event is now updated. It will be reviewed again before it is displayed on the website and digital signs.');
                $('#submit-btn').val('Success!');
                setTimeout(function(){ document.location.href = "detail/"+resp; }, 5000);
              <?php } else { ?>
                $('#alert-success-text').text('Your event was successfully uploaded and will be reviewed. You will be redirected to your event in 5 seconds.');
                $('#submit-btn').val('Success!');
                setTimeout(function(){ document.location.href = "detail/"+resp; }, 5000);
                setCookie('event'+resp, $('#token').val(), 7);
              <?php } ?>
            } else {
              $('#alert-success-text').text(resp);
              $('#submit-btn').val('Submit event for review');
            }
          },
          failure: function(resp) {
            console.log(resp);
          }
        });
      }
    }

    $('#description').on('input', function() {
      var left = $(this).val().length;
      $('#chars-left').text(', ' + (200-left) + ' characters left');
    });
    $('#img').on('change', function() {
      $('#filename').text('You selected ' + $(this)[0].files[0].name);
    });
    $('#date').on('input', function() {
      var date1 = $(this);
      var date2 = $('#date2');
      if (date2.val().length == 0) {
        date2.val(date1.val());
      }
    });

    var sponsors = <?php echo json_encode(array_column($db->query('SELECT sponsor FROM calendar_sponsors ORDER BY sponsor ASC')->fetchAll(), 'sponsor')); ?>;
    var locations = <?php echo json_encode(array_column($db->query('SELECT location FROM calendar_locs ORDER BY location ASC')->fetchAll(), 'location')); ?>;

    $(function() { // init autocomplete and datepicker
      var loc = $('#loc_id');
      var fetch_street_address = function(loc) {
        $.get("/calendar/includes/fetch-street-address.php", {loc: loc}, function(resp) {
          if (resp) {
            $('#street_addr').val(resp);
            $('#street_addr').prop('disabled', true);
            $('#street_addr_valid').text('Please do not edit this field as this location already has a street address.');
          } else {
            $('#street_addr').val('');
            $('#street_addr').prop('disabled', false);
            $('#street_addr_valid').text('Please enter a street address for this location.');
          }
        }, 'text');
      };
      fetch_street_address(loc.val());
      loc.autocomplete({
        source: locations
      });
      loc.on('autocompletechange', function(event, ui) {
        if (ui.item === null) {
          var all_good = true;
          for (var i = locations.length - 1; i >= 0; i--) {
            if (locations[i].toLowerCase().indexOf(loc.val().toLowerCase()) !== -1) {
              loc.addClass('is-invalid');
              $('#invalid-feedback-loc').text(loc.val()+' is too similiar to another location that already exists, '+locations[i]);
              all_good = false;
              break;
            }
          }
          if (all_good) {
            $('#invalid-feedback-loc').text('');
            loc.removeClass('is-invalid');
          }
          $('#street_addr').val('');
          $('#street_addr').prop('disabled', false);
          $('#street_addr_valid').text('Please enter a street address for this location.');
        } else { // fetch the street address for this event
          $('#invalid-feedback-loc').text('');
          loc.removeClass('is-invalid');
          fetch_street_address(loc.val());
        }
      });
      init_sponsor_fields();
      init_datepicker();
    });
    function init_sponsor_fields() {
      $.each(sponsor_fields, function(i, v) {
        v.autocomplete({
          source: sponsors
        });
        v.on('autocompletechange', function(event, ui) {
          var new_sponsor = true;
          sponsors.forEach(function(sponsor) {
            if (sponsor.toLowerCase() == v.val().toLowerCase()) {
              new_sponsor = false;
            }
          })
          if (new_sponsor) { // entered sponsor not in sponsors variable; (ui.item===null) should check this but its not working?
            // only allow new sponsors that are not a substring of an existing sponsor in the sponsors variable
            var all_good = true;
            for (var i = sponsors.length - 1; i >= 0; i--) {
              if (sponsors[i].toLowerCase().indexOf(v.val().toLowerCase()) !== -1) {
                v.addClass('is-invalid');
                $('#invalid-feedback' + v.data('sponsor')).text(v.val()+' is too similiar to another sponsor that already exists, '+sponsors[i]);
                all_good = false;
                break;
              }
            }
            if (all_good) {
              $('#invalid-feedback' + v.data('sponsor')).text('');
              v.removeClass('is-invalid');
            }
          } else {
            v.removeClass('is-invalid');
            $('#invalid-feedback' + v.data('sponsor')).text('');
          }
        });
      });
    }
    function init_datepicker() {
      $( "#date" ).datepicker({
        onSelect: function(dateText) {
          $('#date2').val(this.value);
        }
      });
      $( "#date2" ).datepicker();
      $( "#dup_date" ).datepicker();
      $( "#dup_date2" ).datepicker();
      // also found this little library...
      $('#time').timepicker();
      $('#time2').timepicker();
      $( "#dup_time" ).timepicker();
      $( "#dup_time2" ).timepicker();
    }

    window.onbeforeunload = function() {
      $('#event-form').serializefiles().forEach(function(a, b, formdata) {
        for (var pair of formdata.entries()) { // https://developer.mozilla.org/en-US/docs/Web/API/FormData/entries
          setCookie(pair[0], pair[1], 1);
        }
      });
    }

    // https://stackoverflow.com/a/24103596/2624391
    function setCookie(name,value,days) {
      var expires = "";
      if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
      }
      document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }
    function getCookie(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for (var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
      }
      return null;
    }
    function eraseCookie(name) {   
      document.cookie = name+'=; Max-Age=-99999999;';  
    }
  </script>
  </body>
</html>