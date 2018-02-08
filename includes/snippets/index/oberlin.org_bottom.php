</div> <!-- /.container -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

<script>
  $('#newsletter-form').on('submit', function(e) {
    e.preventDefault();
    var email = $('#newsletter-email').val();
    if (email) {
      $.post( "includes/newsletter_sub.php", { email: email } );
      alert('You have subscribed to our newsletter');
    }
  });
  var limit = 5, offset = 5, scroll_done = false;
  $(window).scroll(function() { // https://stackoverflow.com/a/21561584/2624391
    var hT = $('#bottom-of-events').offset().top,
        hH = $('#bottom-of-events').outerHeight(),
        wH = $(window).height(),
        wS = $(this).scrollTop();
    if (!scroll_done && wS > (hT+hH-wH)) {
      scroll_done = true;
      load_events();
    }
  });
  function load_events() {
    console.log('Loading more events');
    $.get("includes/load_events.php", {limit:limit, offset:offset}, function(data) {
      if (data == '0') {
        scroll_done = true;
        $('#bottom-of-events').html('<p>You have reached the end of the feed.</p>');
        $('#loader').remove();
      } else {
        scroll_done = false;
        $('#bottom-of-events').before(data);
        offset += limit;
        sidebar_filters();
      }
    });
  }

  var month = <?php echo date('n') ?>, year = <?php echo date('Y') ?>;
  function next_month() {
    if (month === 12) {
      month = 1;
      year = year + 1;
    } else {
      month = month + 1;
      year = year;
    }
  }
  function prev_month() {
    if (month === 1) {
      month = 12;
      year = year - 1;
    } else {
      month = month - 1;
      year = year;
    }
  }
  $(document).on('click', '#next-month-btn', function(e) {
    e.preventDefault();
    console.log(month, year);
    next_month();
    console.log(month, year);
    load_small_cal();
  });
  $(document).on('click', '#prev-month-btn', function(e) {
    e.preventDefault();
    console.log(month, year);
    prev_month();
    console.log(month, year);
    load_small_cal();
  });
  function load_small_cal() {
    $('[data-toggle="popover"]').popover('dispose');
    $.get("includes/load_calendar.php", {month:month, year:year}, function(data) {
      $('#small-cal').html(data);
      $('[data-toggle="popover"]').popover({ trigger: "hover" });
    });
  }
  var sponsors = <?php echo json_encode($cal->sponsors) . ";\n"; ?>
  var current_filters = {'eventtype': 'All', 'eventloc': 'All', 'eventsponsor': 'All'};
  function sidebar_filters() {
    var tmp = scroll_done;
    scroll_done = true;
    $('.iterable-event').each(function() {
      $(this).css('display', '');
      for (var type in current_filters) {
        if (current_filters[type] !== 'All' && type === 'eventsponsor') { // eventsponsor is an array so have to iterate
          var shown = false,
              type_val = $(this).data('eventsponsor').toString().split('$SEP$');
          $.each(type_val, function( index, value ) {
            if (value != '') {
              // var this_sponsor = sponsors[value];
              // console.log('eventsponsor', this_sponsor, current_filters[type], value);
              if (value == current_filters[type]) {
                shown = true;
              }
            }
          });
          if (!shown) {
            $(this).css('display', 'none');
            break;
          }
        } else {
          var type_val = $(this).data(type);
          if (current_filters[type] !== 'All' && current_filters[type] != type_val) {
            // console.log(type_val, current_filters[type]);
            $(this).css('display', 'none');
            break;
          }
        }
      }
    });
    scroll_done = tmp;
  }
  $('.event-type-toggle').on('click', function(e) {
    e.preventDefault();
    $('.event-type-toggle').removeClass('active');
    $(this).addClass('active');
    current_filters['eventtype'] = $(this).data('value');
    sidebar_filters();
  });
  $('#event-loc-toggle').on('change', function(e) {
    e.preventDefault();
    current_filters['eventloc'] = this.value;
    sidebar_filters();
  });
  $('#event-sponsor-toggle').on('change', function(e) {
    e.preventDefault();
    current_filters['eventsponsor'] = this.value;
    sidebar_filters();
  });
  $('#sort-date').on('click', function(e) {
    e.preventDefault();
    if ($(this).html() === 'Date ↓') {
      $(this).html('Date &uarr;');
    } else {
      $(this).html('Date &darr;');
    }
    e.preventDefault();
    var sort = []
    $('.iterable-event').each(function() {
      var div = $(this);
      sort.push(div.data('date') + ',' + div.attr('id'));
    });
    sort.reverse();
    var prev_id = 'top-of-events';
    for (var i = 0; i < sort.length; i++) {
      var id = sort[i].split(',')[1];
      $('#' + id).insertAfter('#' + prev_id);
      prev_id = id;
    }
  });
  $('#search').on('input', function() {
    $('.iterable-event').each(function() {
      var query = $('#search').val().toLowerCase(),
          card = $(this);
      if (card.data('name').toLowerCase().indexOf(query) === -1) {
        card.css('display', 'none');
      } else {
        card.css('display', 'block');
      }
    });
  });

  $(function () {
    $('[data-toggle="popover"]').popover({ trigger: "hover" });
  });
  // $('.day').on('click', function() {
  //   var date = $(this).data('mdy');
  //   $('.iterable-event').each(function() {
  //     if ($(this).data('mdy') != date) {
  //       $(this).css('display', 'none');
  //     } else {
  //       $(this).css('display', '');
  //     }
  //   });
  // });
</script>
</body>
</html>