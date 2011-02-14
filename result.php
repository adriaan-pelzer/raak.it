<?php
include "header.php";
?>
        <section id="content">
            <p>
                <span id="label">Your shortened url:</label>
                <span id="url"><mark><?php echo $_POST['url_in']; ?></mark></span>
            </p>
            <form action="result.php" method="get" name="shorten">
                <label for="url_in">Please Enter URL to shorten</label>
                <input type="url" name="url_in" placeholder="url to be shortened"  required id="text" />
                <input type="submit" value="Shorten" id ="button" />
            </form>
        </section>
        <section id="top5">
        <h2>Top 5 links</h2>
        <ol>
            <li><a href="#">one</a></li>
            <li><a href="#">two</a></li>
            <li><a href="#">three</a></li>
            <li><a href="#">four</a></li>
            <li><a href="#">five</a></li>
        </ol>
        </section>
<?php
include "footer.php";
?>
