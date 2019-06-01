</div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('#eventModal').on('show.bs.modal', function (event) {
      	var dest = $('#modal-body');
      	dest.html('');
			  var button = $(event.relatedTarget);
			  $('#eventModalLabel').text('Events on '+button.data('date'));
			  var ids = button.data('ids');
			  var titles = JSON.parse(button.data('titles'));
			  var descripts = JSON.parse(button.data('descripts'));
			  for (var i=0; i < ids.length; i++) { 
			  	dest.append('<h4>'+titles[i]+'</h4>'+'<p>'+descripts[i]+'</p><p><a class="btn btn-primary btn-sm" href="/calendar/detail/'+ids[i]+'">View more</a></p>');
			  }
			})
    </script>
  </body>
</html>