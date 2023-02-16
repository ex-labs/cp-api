    <div style="width: 1000px; margin: 0 auto; padding: 50px;">
        <form action="index-tv-test.php" method="post">
        <input type="hidden" name="dev" value="apple">
        <h4>Method</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="request" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <h4>UID</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="uid" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <div style="padding: 20px 0 0 0;"><input type="submit" value="Test This" /></div>
        </form>

        <div id="debug" style="padding: 100px 0 100px 0;"><?php echo $debug;?></div>


    </div>
