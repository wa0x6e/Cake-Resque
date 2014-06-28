</article>
</div>
</div>
<?php
    $js = getJsFiles();
    foreach ($js as $name => $url) {
        echo '<script type="text/javascript" src="' . $url . '"></script>';
    }
?>
<script type="text/javascript">
	hljs.initHighlightingOnLoad();

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-34599102-1']);
    _gaq.push(['_trackPageview']);

    (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
</script>
</body>
</html>