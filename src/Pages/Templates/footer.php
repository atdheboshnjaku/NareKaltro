

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
</body>
</html>