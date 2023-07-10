<?php
use Fin\Narekaltro\App\Session;

$session = new Session();
if ($session->isLogged()) { ?>

    <div class="footer">
        <p>
            <?php echo date('Y'); ?> &copy; <a href="https://bluwebs.com" target="_blank">Bluwebs</a> - All Rights Reserved
        </p>
    </div>
    <!-- App view -->
    </div>
    <!-- Main body container -->
<?php } ?>

</div>
<script type="text/javascript">

    const asideElement = document.querySelector('aside');
    const toggleButton = document.querySelector('.mob-menu'); // Selector for your toggle button div
    const toggleButton2 = document.querySelector('.mob-menu-2'); // Selector for your toggle button div

    toggleButton.addEventListener('click', () => {
        asideElement.classList.toggle('show-aside');
    });

    toggleButton2.addEventListener('click', () => {
        asideElement.classList.toggle('show-aside');
    });

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
    crossorigin="anonymous"></script>
</body>

</html>