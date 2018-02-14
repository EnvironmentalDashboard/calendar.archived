</div> <!-- ./padding -->
<?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/footer.php'; ?>
</div> <!-- /.container -->
    <?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/js.php'; ?>
    <script>
      $('.alert > button').on('click', function() {
        $('.alert').css('display', 'none');
      });
    </script>
  </body>
</html>