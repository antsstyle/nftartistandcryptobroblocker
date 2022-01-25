<?php
require __DIR__ . '/vendor/autoload.php';

use Antsstyle\NFTCryptoBlocker\Core\CachedVariables;
use Antsstyle\NFTCryptoBlocker\Core\Core;
use Antsstyle\NFTCryptoBlocker\Core\CoreDB;
use Antsstyle\NFTCryptoBlocker\Core\Config;
use Antsstyle\NFTCryptoBlocker\Core\Session;
use Antsstyle\NFTCryptoBlocker\Credentials\APIKeys;
use Abraham\TwitterOAuth\TwitterOAuth;

Session::checkSession();

$connection = new TwitterOAuth(APIKeys::consumer_key, APIKeys::consumer_secret);
try {
    $response = $connection->oauth("oauth/request_token", ["oauth_callback" => "https://antsstyle.com/nftcryptoblocker/results"]);
    $httpcode = $connection->getLastHttpCode();
    if ($httpcode != 200) {
        error_log("Failed to get request token!");
        // Show error page
    }
} catch (\Exception $e) {
    
}
$oauth_token = $response['oauth_token'];
$oauth_token_secret = $response['oauth_token_secret'];
$oauth_callback_confirmed = $response['oauth_callback_confirmed'];
$_SESSION['oauth_token'] = $oauth_token;
$_SESSION['oauth_token_secret'] = $oauth_token_secret;
$oauth_token_array['oauth_token'] = $oauth_token;
try {
    $url = $connection->url('oauth/authenticate', array('oauth_token' => $oauth_token));
} catch (\Exception $e) {
    
}

$blockCount = CoreDB::getCachedVariable(CachedVariables::CACHED_TOTAL_BLOCKS_COUNT);
if ($blockCount !== null && $blockCount !== false) {
    if ($blockCount > 1000000000) {
        $blockCount = round($blockCount / 1000000000, 2, PHP_ROUND_HALF_DOWN) . "B";
    } else if ($blockCount > 1000000) {
        $blockCount = round($blockCount / 1000000, 2, PHP_ROUND_HALF_DOWN) . "M";
    } else if ($blockCount > 1000) {
        $blockCount = floor($blockCount / 1000) . "K";
    }
}
$muteCount = CoreDB::getCachedVariable(CachedVariables::CACHED_TOTAL_MUTES_COUNT);
if ($muteCount !== null && $muteCount !== false) {
    if ($muteCount > 1000000000) {
        $muteCount = round($muteCount / 1000000000, 2, PHP_ROUND_HALF_DOWN) . "B";
    } else if ($muteCount > 1000000) {
        $muteCount = round($muteCount / 1000000, 2, PHP_ROUND_HALF_DOWN) . "M";
    } else if ($muteCount > 1000) {
        $muteCount = floor($muteCount / 1000) . "K";
    }
}
?>

<html>
    <head>
        <link rel="stylesheet" href="main.css" type="text/css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:site" content="@antsstyle" />
        <meta name="twitter:title" content="NFT Artist & Cryptobro Blocker" />
        <meta name="twitter:description" content="Auto-blocks or auto-mutes NFT artists and cryptobros on your timeline and/or in your mentions." />
        <meta name="twitter:image" content="<?php echo Config::CARD_IMAGE_URL; ?>" />
    </head>
    <title>
        NFT Artist & Cryptobro Blocker
    </title>
    <body>
        <div class="main">
            <?php Core::echoSideBar(); ?>
            <h1>NFT Artist & Cryptobro Blocker</h1>
            <p>
                This app can automatically block or mute cryptobros and NFT artists for you.
            </p>
            <?php
            if ((!is_null($blockCount) && $blockCount !== false) || (!is_null($muteCount) && $muteCount !== false)) {
                echo "<p>So far, it has performed ";
                if ((!is_null($blockCount) && $blockCount !== false)) {
                    echo "<b>" . $blockCount . "</b> blocks ";
                }
                if ((!is_null($muteCount) && $muteCount !== false)) {
                    if ((!is_null($blockCount) && $blockCount !== false)) {
                        echo "and ";
                    }
                    echo "<b>" . $muteCount . "</b> mutes ";
                }
                echo "on behalf of users.</p>";
            }
            ?>
            <p>
                Once you sign in, you will be taken to the settings page where you can decide what conditions to set. 
                The app will not block or mute anything until you save your settings.
            </p>
            <p>
                To use this app or change your settings, you must first sign in with Twitter. Use the button below to proceed.
            </p>
            <br/>
            <a href=<?php echo "$url" ?>>
                <img alt="Sign in with Twitter" src="src/images/signinwithtwitter.png"
                     width=158" height="28">
            </a>
            <br/><br/>
            <div class="halted">
                NFT Artist & Cryptobro Blocker is currently not accepting any new signups, while scaling issues are addressed. You can still sign in to the 
                app, but only existing users are able to change settings at this time.
                <br/><br/>
                Current estimate for when new signups will begin: 22 Feb. Note this is a wild guess at the moment and is subject to change.
            </div>
            <br/><br/>
            <button class="collapsible">FAQs</button>
            <div class="content">
                <h3>Does this app perform any actions if I sign in but don't save any settings?</h3>
                <p>
                    No. The app will not perform any actions on your account until you save your settings. 
                    If you do not save any settings, it will do nothing.
                </p>
                <h3>Why does this app need so many permissions?</h3>
                <p>
                    The Twitter API only allows developers to request 'read', 'write' and 'direct message' permissions. 
                    As this app needs to be able to read your home timeline, and block or mute users for you (which count as "writes"), 
                    it needs both read and write permissions.
                    <br/><br/>
                    It does not request direct message permissions as it does not interact with your direct messages in any way.
                </p>
                <h3>Is the source code for this app available?</h3>
                <p>
                    Yes. You can find it here: <a href="https://github.com/antsstyle/nftcryptoblocker">https://github.com/antsstyle/nftcryptoblocker</a>
                </p>
            </div>
        </div>
    </body>
    <script src="Collapsibles.js"></script>
</html>
