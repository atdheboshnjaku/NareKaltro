

<?php
    use Fin\Narekaltro\App\Session;
    $session = new Session();
    if($session->isLogged()) { ?>

    <div class="footer">
        <?php echo date('Y'); ?> &copy; <a href="https://bluwebs.com" target="_blank">Bluwebs</a> - All Rights Reserved
    </div>
    <!-- App view -->
    </div>
<!-- Main body container -->
<?php } ?>

</div>
<script type="text/javascript">


</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>