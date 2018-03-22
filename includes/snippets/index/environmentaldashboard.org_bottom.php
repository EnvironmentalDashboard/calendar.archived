</div> <!-- ./padding -->
<?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/footer.php'; ?>
</div> <!-- /.container -->
<?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/js.php'; ?>
<script>
  $('#newsletter-form').on('submit', function(e) {
    e.preventDefault();
    var email = $('#newsletter-email').val();
    if (email) {
      $.post( "includes/newsletter_sub.php", { email: email } );
      alert('Please check your inbox and spam folder for a confirmation email');
    }
  });
  var limit = 5, offset = 5, scroll_done = false; // start offset at 5 bc first 5 events already loaded
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
    var query = $('#search').val();
    if (query == '') {
      var payload = {limit:limit, offset:offset};
      var end_of_feed = '<p id="end-of-feed">You have reached the end of the feed.</p>';
    } else {
      var payload = {limit:limit, offset:offset, search:query};
      var end_of_feed = '<p id="end-of-feed">There are no more results for "'+(query.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
        return '&#'+i.charCodeAt(0)+';'; // https://stackoverflow.com/a/18750001/2624391
      }))+'"</p>';
    }
    $.get("includes/load_events.php", payload, function(data) {
      if (data == '0') {
        scroll_done = true;
        $('#bottom-of-events').html(end_of_feed);
        $('#loader').hide();
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
    next_month();
    load_small_cal();
  });
  $(document).on('click', '#prev-month-btn', function(e) {
    e.preventDefault();
    prev_month();
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
  var stopped_typing = null;
  $('#search').on('input', function() {
    $('.iterable-event').each(function() { // hide/show events already on page
      var query = $('#search').val().toLowerCase(),
          card = $(this);
      if (card.data('name').toLowerCase().indexOf(query) === -1) {
        card.css('display', 'none');
      } else {
        card.css('display', 'block');
      }
    });
    // search db for more events because the query has changed
    clearTimeout(stopped_typing);
    stopped_typing = setTimeout(function(){
      $('.iterable-event').remove();
      offset = 0;
      if (scroll_done) {
        $('#loader').show();
        $('#end-of-feed').remove();
        scroll_done = false;
      }
      load_events();
    }, 250);

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
  window.onscroll = function() {scrollFunction()};
  function scrollFunction() {
    if (document.body.scrollTop > 700 || document.documentElement.scrollTop > 700) {
      $("#to-top").css('display', "block");
    } else {
      $("#to-top").css('display', "none");
    }
  }
  function topFunction() {
    window.scroll({
      top: 0, 
      left: 0, 
      behavior: 'smooth' 
    });
  }
</script>
</body>
</html>