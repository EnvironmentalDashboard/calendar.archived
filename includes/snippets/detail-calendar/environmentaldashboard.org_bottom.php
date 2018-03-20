</div> <!-- ./padding -->
<?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/footer.php'; ?>
</div> <!-- /.container -->
    <?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/js.php'; ?>
    <script>
      $('#eventModal').on('show.bs.modal', function (event) {
      	var dest = $('#modal-body');
      	dest.html('');
			  var button = $(event.relatedTarget)
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