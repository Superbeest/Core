<?php
/**
* Browser.class.php
*
* Copyright c 2015, SUPERHOLDER. All rights reserved.
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or at your option any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301  USA
*/


namespace System\HTTP\Visitor;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* This class contains functionality for the identification of the client browser.
* It also identifies other client specific browser treats and behaviour patterns.
* @package \System\HTTP\Visitor
*/
class Browser extends \System\HTTP\ServerBase
{
    /**
    * @var array listing of browsers.
    */
    private static $browsers = array(
            'edge',
            'msie',
            'firefox',
            'konqueror',
            'chrome',
            'safari',
            'netscape',
            'navigator',
            'opera',
            'mosaic',
            'lynx',
            'amaya',
            'omniweb',
            'avant',
            'camino',
            'flock',
            'seamonkey',
            'aol',
            'trident',
            'mozilla',
            'gecko',
            'superholder');

    /**
    * @var array listing of Windows versions corresponding to
    */
    private static $windowsVersions = array(
            'Windows NT 5.1' => 'Windows XP',
            'Windows NT 5.2' => 'Windows Server 2003/XP64',
            'Windows NT 6.0' => 'Windows Vista',
            'Windows NT 6.1' => 'Windows 7',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 10.0' => 'Windows 10');

    /**
    * Returns the user agent string.
    * @return string The user agent string used by the client.
    */
    public static final function getUserAgent()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_USER_AGENT'];
    }

    /**
    * Returns the browser of the current user.
    * This is returned as a string. The string is semicryptic, but identifiable.
    * @param string The user agent to decode. If omitted, the current visitor is used
    * @return string The browser of the user, or 'unknown'
    */
    public static final function getBrowser($userAgent = '')
    {
        if (empty($userAgent))
        {
        	$userAgent = self::getUserAgent();
		}

        foreach (self::$browsers as $browser)
        {
            if (stristr($userAgent, $browser) !== false)
            {
                return $browser;
            }
        }

        return 'unknown';
    }

    /**
    * Returns the current OS of the user.
    * Possible return values are 'unknown', '*nix', 'Max', 'Windows'. It is possible to detect specific versions of Windows
    * @param bool If true, the function tries to match specific Windows versions, false returns 'Windows'
    * @param string The user agent to decode. If omitted, the current visitor is used
    * @return string The os of the user.
    */
    public static final function getClientOS($extendedWindowsInfo = true, $userAgent = '')
    {
		if (empty($userAgent))
		{
        	$userAgent = self::getUserAgent();
		}


        switch (true)
        {
			case stripos($userAgent, 'ipad') !== false:
        		$os =  'IOS (iPad)';
        		break;
        	case stripos($userAgent, 'ipod') !== false:
        		$os =  'IOS (iPod)';
        		break;
        	case stripos($userAgent, 'iphone') !== false:
        		$os =  'IOS (iPhone)';
        		break;
        	case stripos($userAgent, 'linux') !== false:
        		$os =  '*nix';
        		break;
        	case stripos($userAgent, 'superholder') !== false:
        		$os = 'SUPERHOLDER';
        		break;
        	case (stripos($userAgent, 'macintosh') !== false) || (stripos($userAgent, 'mac platform x') !== false):
        		$os =  'Mac';
        		break;
        	case (stripos($userAgent, 'windows') !== false) || (stripos($userAgent, 'win32') !== false):
        		$os =  'Windows';

	            if ($extendedWindowsInfo)
	            {
	                foreach (self::$windowsVersions as $agent=>$version)
	                {
	                    if (stristr($userAgent, $agent) !== false)
	                    {
	                        $os = $version;
	                        break;
	                    }
	                }
	            }
	            break;
        	default:
        		$os = 'unknown';
		}

        return $os;
    }

    /**
    * Returns the current version of the detected browser, or '1' (int) for unknown.
    * @param string The user agent to decode. If omitted, the current visitor is used
    * @return string The version of the used browser.
    */
    public static final function getBrowserVersion($userAgent = '')
    {
    	if (empty($userAgent))
    	{
        	$userAgent = self::getUserAgent();
		}

        foreach (self::$browsers as $browser)
        {
            if (stristr($userAgent, $browser) !== false)
            {
                $version = '';

                $versionPos = stripos($userAgent, $browser) + mb_strlen($browser) + 1;
                for ($i = $versionPos; $i < mb_strlen($userAgent); $i++)
                {
                    $s = trim(mb_substr($userAgent, $i, 1));
                    if ((mb_strlen($s) == 0) ||
                        ($s == ';'))
                    {
                        break;
                    }
                    $version .= $s;
                }
                return $version;
            }
        }

        return 1;
    }


    /**
    * Returns the server ACCEPT variable send in the headers of the request.
    * This shows the support that the browser has.
    * @return string The support string send by the browser.
    */
    public static final function getAccept()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_ACCEPT'];
    }

    /**
    * Returns the supported encoding techniques.
    * @return string The supported encoding techniques.
    */
    public static final function getEncoding()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_ACCEPT_ENCODING'];
    }

    /**
    * Returns the supported charsets
    * @return string The supported charsets.
    */
    public static final function getCharset()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_ACCEPT_CHARSET'];
    }

    /**
    * Determin whether or not the current user is using a mobile platform.
    * The check for the mobile site is based on, among others, the user agent.
    * Therefor this check isn't waterproof, but will be sufficient for most uses.
    * @param bool True to detect ios devices
    * @param bool True to detect android devices
    * @param bool true to detect opera mini
    * @param bool True to detect blackberry devices
    * @param bool True to detect palm devices
    * @param bool True to detect windows mobile
    * @return bool True if the detected platform is mobile, false otherwise.
    */
    public static final function isMobilePlatform($detectIOS = true, $detectAndroid = true, $detectOpera = true, $detectBlackBerry = true, $detectPalm = true, $detectWindowsMobile = true)
    {
        $isMobile = false;

        $accept = self::getAccept();
        $agent = self::getUserAgent();
        $server = self::getServerHandle();

        switch (true)
        {
            case ((stripos($agent, 'ipod') !== false) || (stripos($agent, 'iphone') !== false) || (stripos($agent, 'iPad') !== false)):
                $isMobile = $detectIOS;
                break;
            case (stripos($agent, 'android') !== false):
                $isMobile = $detectAndroid;
                break;
            case (stripos($agent, 'opera mini') !== false):
                $isMobile = $detectOpera;
                break;
            case (stripos($agent, 'blackberry') !== false):
                $isMobile = $detectBlackBerry;
                break;
            case (preg_match('/(palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i', $agent)):
                $isMobile = $detectPalm;
                break;
            case (preg_match('/(windows ce; ppc;|windows ce; smartphone;|windows ce; iemobile|Windows Phone OS 7|ZuneWP7|IEMobile)/i', $agent)):
                $isMobile = $detectWindowsMobile;
                break;
            case (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|pda|psp|treo)/i', $agent)):
                $isMobile = true;
                break;
            case ((strpos($accept,'text/vnd.wap.wml') > 0) || (strpos($accept, 'application/vnd.wap.xhtml+xml') > 0));
                $isMobile = true;
                break;
            case ((isset($server['HTTP_X_WAP_PROFILE'])) || (isset($server['HTTP_PROFILE']))):
                $isMobile = true;
                break;
            case (in_array(mb_strtolower(mb_substr($agent, 0, 4)), array('1207' => '1207', '3gso' => '3gso', '4thp' => '4thp', '501i' => '501i', '502i' => '502i', '503i' => '503i', '504i' => '504i', '505i' => '505i', '506i' => '506i', '6310' => '6310', '6590' => '6590', '770s' => '770s', '802s' => '802s', 'a wa' => 'a wa',
                'acer' => 'acer', 'acs-' => 'acs-', 'airn' => 'airn', 'alav' => 'alav', 'asus' => 'asus', 'attw' => 'attw', 'au-m' => 'au-m', 'aur ' => 'aur ', 'aus ' => 'aus ', 'abac' => 'abac', 'acoo' => 'acoo', 'aiko' => 'aiko', 'alco' => 'alco', 'alca' => 'alca', 'amoi' => 'amoi', 'anex' => 'anex', 'anny' => 'anny',
                'anyw' => 'anyw', 'aptu' => 'aptu', 'arch' => 'arch', 'argo' => 'argo', 'bell' => 'bell', 'bird' => 'bird', 'bw-n' => 'bw-n', 'bw-u' => 'bw-u', 'beck' => 'beck', 'benq' => 'benq', 'bilb' => 'bilb', 'blac' => 'blac', 'c55/' => 'c55/', 'cdm-' => 'cdm-', 'chtm' => 'chtm', 'capi' => 'capi', 'comp' => 'comp',
                'cond' => 'cond', 'craw' => 'craw', 'dall' => 'dall', 'dbte' => 'dbte', 'dc-s' => 'dc-s', 'dica' => 'dica', 'ds-d' => 'ds-d', 'ds12' => 'ds12', 'dait' => 'dait', 'devi' => 'devi', 'dmob' => 'dmob', 'doco' => 'doco', 'dopo' => 'dopo', 'el49' => 'el49', 'erk0' => 'erk0', 'esl8' => 'esl8', 'ez40' => 'ez40',
                'ez60' => 'ez60', 'ez70' => 'ez70', 'ezos' => 'ezos', 'ezze' => 'ezze', 'elai' => 'elai', 'emul' => 'emul', 'eric' => 'eric', 'ezwa' => 'ezwa', 'fake' => 'fake', 'fly-' => 'fly-', 'fly_' => 'fly_', 'g-mo' => 'g-mo', 'g1 u' => 'g1 u', 'g560' => 'g560', 'gf-5' => 'gf-5', 'grun' => 'grun', 'gene' => 'gene',
                'go.w' => 'go.w', 'good' => 'good', 'grad' => 'grad', 'hcit' => 'hcit', 'hd-m' => 'hd-m', 'hd-p' => 'hd-p', 'hd-t' => 'hd-t', 'hei-' => 'hei-', 'hp i' => 'hp i', 'hpip' => 'hpip', 'hs-c' => 'hs-c', 'htc ' => 'htc ', 'htc-' => 'htc-', 'htca' => 'htca', 'htcg' => 'htcg', 'htcp' => 'htcp', 'htcs' => 'htcs',
                'htct' => 'htct', 'htc_' => 'htc_', 'haie' => 'haie', 'hita' => 'hita', 'huaw' => 'huaw', 'hutc' => 'hutc', 'i-20' => 'i-20', 'i-go' => 'i-go', 'i-ma' => 'i-ma', 'i230' => 'i230', 'iac' => 'iac', 'iac-' => 'iac-', 'iac/' => 'iac/', 'ig01' => 'ig01', 'im1k' => 'im1k', 'inno' => 'inno', 'iris' => 'iris',
                'jata' => 'jata', 'java' => 'java', 'kddi' => 'kddi', 'kgt' => 'kgt', 'kgt/' => 'kgt/', 'kpt ' => 'kpt ', 'kwc-' => 'kwc-', 'klon' => 'klon', 'lexi' => 'lexi', 'lg g' => 'lg g', 'lg-a' => 'lg-a', 'lg-b' => 'lg-b', 'lg-c' => 'lg-c', 'lg-d' => 'lg-d', 'lg-f' => 'lg-f', 'lg-g' => 'lg-g', 'lg-k' => 'lg-k',
                'lg-l' => 'lg-l', 'lg-m' => 'lg-m', 'lg-o' => 'lg-o', 'lg-p' => 'lg-p', 'lg-s' => 'lg-s', 'lg-t' => 'lg-t', 'lg-u' => 'lg-u', 'lg-w' => 'lg-w', 'lg/k' => 'lg/k', 'lg/l' => 'lg/l', 'lg/u' => 'lg/u', 'lg50' => 'lg50', 'lg54' => 'lg54', 'lge-' => 'lge-', 'lge/' => 'lge/', 'lynx' => 'lynx', 'leno' => 'leno',
                'm1-w' => 'm1-w', 'm3ga' => 'm3ga', 'm50/' => 'm50/', 'maui' => 'maui', 'mc01' => 'mc01', 'mc21' => 'mc21', 'mcca' => 'mcca', 'medi' => 'medi', 'meri' => 'meri', 'mio8' => 'mio8', 'mioa' => 'mioa', 'mo01' => 'mo01', 'mo02' => 'mo02', 'mode' => 'mode', 'modo' => 'modo', 'mot ' => 'mot ', 'mot-' => 'mot-',
                'mt50' => 'mt50', 'mtp1' => 'mtp1', 'mtv ' => 'mtv ', 'mate' => 'mate', 'maxo' => 'maxo', 'merc' => 'merc', 'mits' => 'mits', 'mobi' => 'mobi', 'motv' => 'motv', 'mozz' => 'mozz', 'n100' => 'n100', 'n101' => 'n101', 'n102' => 'n102', 'n202' => 'n202', 'n203' => 'n203', 'n300' => 'n300', 'n302' => 'n302',
                'n500' => 'n500', 'n502' => 'n502', 'n505' => 'n505', 'n700' => 'n700', 'n701' => 'n701', 'n710' => 'n710', 'nec-' => 'nec-', 'nem-' => 'nem-', 'newg' => 'newg', 'neon' => 'neon', 'netf' => 'netf', 'noki' => 'noki', 'nzph' => 'nzph', 'o2 x' => 'o2 x', 'o2-x' => 'o2-x', 'opwv' => 'opwv', 'owg1' => 'owg1',
                'opti' => 'opti', 'oran' => 'oran', 'p800' => 'p800', 'pand' => 'pand', 'pg-1' => 'pg-1', 'pg-2' => 'pg-2', 'pg-3' => 'pg-3', 'pg-6' => 'pg-6', 'pg-8' => 'pg-8', 'pg-c' => 'pg-c', 'pg13' => 'pg13', 'phil' => 'phil', 'pn-2' => 'pn-2', 'pt-g' => 'pt-g', 'palm' => 'palm', 'pana' => 'pana', 'pire' => 'pire',
                'pock' => 'pock', 'pose' => 'pose', 'psio' => 'psio', 'qa-a' => 'qa-a', 'qc-2' => 'qc-2', 'qc-3' => 'qc-3', 'qc-5' => 'qc-5', 'qc-7' => 'qc-7', 'qc07' => 'qc07', 'qc12' => 'qc12', 'qc21' => 'qc21', 'qc32' => 'qc32', 'qc60' => 'qc60', 'qci-' => 'qci-', 'qwap' => 'qwap', 'qtek' => 'qtek', 'r380' => 'r380',
                'r600' => 'r600', 'raks' => 'raks', 'rim9' => 'rim9', 'rove' => 'rove', 's55/' => 's55/', 'sage' => 'sage', 'sams' => 'sams', 'sc01' => 'sc01', 'sch-' => 'sch-', 'scp-' => 'scp-', 'sdk/' => 'sdk/', 'se47' => 'se47', 'sec-' => 'sec-', 'sec0' => 'sec0', 'sec1' => 'sec1', 'semc' => 'semc', 'sgh-' => 'sgh-',
                'shar' => 'shar', 'sie-' => 'sie-', 'sk-0' => 'sk-0', 'sl45' => 'sl45', 'slid' => 'slid', 'smb3' => 'smb3', 'smt5' => 'smt5', 'sp01' => 'sp01', 'sph-' => 'sph-', 'spv ' => 'spv ', 'spv-' => 'spv-', 'sy01' => 'sy01', 'samm' => 'samm', 'sany' => 'sany', 'sava' => 'sava', 'scoo' => 'scoo', 'send' => 'send',
                'siem' => 'siem', 'smar' => 'smar', 'smit' => 'smit', 'soft' => 'soft', 'sony' => 'sony', 't-mo' => 't-mo', 't218' => 't218', 't250' => 't250', 't600' => 't600', 't610' => 't610', 't618' => 't618', 'tcl-' => 'tcl-', 'tdg-' => 'tdg-', 'telm' => 'telm', 'tim-' => 'tim-', 'ts70' => 'ts70', 'tsm-' => 'tsm-',
                'tsm3' => 'tsm3', 'tsm5' => 'tsm5', 'tx-9' => 'tx-9', 'tagt' => 'tagt', 'talk' => 'talk', 'teli' => 'teli', 'topl' => 'topl', 'tosh' => 'tosh', 'up.b' => 'up.b', 'upg1' => 'upg1', 'utst' => 'utst', 'v400' => 'v400', 'v750' => 'v750', 'veri' => 'veri', 'vk-v' => 'vk-v', 'vk40' => 'vk40', 'vk50' => 'vk50',
                'vk52' => 'vk52', 'vk53' => 'vk53', 'vm40' => 'vm40', 'vx98' => 'vx98', 'virg' => 'virg', 'vite' => 'vite', 'voda' => 'voda', 'vulc' => 'vulc', 'w3c ' => 'w3c ', 'w3c-' => 'w3c-', 'wapj' => 'wapj', 'wapp' => 'wapp', 'wapu' => 'wapu', 'wapm' => 'wapm', 'wig ' => 'wig ', 'wapi' => 'wapi', 'wapr' => 'wapr',
                'wapv' => 'wapv', 'wapy' => 'wapy', 'wapa' => 'wapa', 'waps' => 'waps', 'wapt' => 'wapt', 'winc' => 'winc', 'winw' => 'winw', 'wonu' => 'wonu', 'x700' => 'x700', 'xda2' => 'xda2', 'xdag' => 'xdag', 'yas-' => 'yas-', 'your' => 'your', 'zte-' => 'zte-', 'zeto' => 'zeto', 'acs-' => 'acs-', 'alav' => 'alav',
                'alca' => 'alca', 'amoi' => 'amoi', 'aste' => 'aste', 'audi' => 'audi', 'avan' => 'avan', 'benq' => 'benq', 'bird' => 'bird', 'blac' => 'blac', 'blaz' => 'blaz', 'brew' => 'brew', 'brvw' => 'brvw', 'bumb' => 'bumb', 'ccwa' => 'ccwa', 'cell' => 'cell', 'cldc' => 'cldc', 'cmd-' => 'cmd-', 'dang' => 'dang',
                'doco' => 'doco', 'eml2' => 'eml2', 'eric' => 'eric', 'fetc' => 'fetc', 'hipt' => 'hipt', 'http' => 'http', 'ibro' => 'ibro', 'idea' => 'idea', 'ikom' => 'ikom', 'inno' => 'inno', 'ipaq' => 'ipaq', 'jbro' => 'jbro', 'jemu' => 'jemu', 'java' => 'java', 'jigs' => 'jigs', 'kddi' => 'kddi', 'keji' => 'keji',
                'kyoc' => 'kyoc', 'kyok' => 'kyok', 'leno' => 'leno', 'lg-c' => 'lg-c', 'lg-d' => 'lg-d', 'lg-g' => 'lg-g', 'lge-' => 'lge-', 'libw' => 'libw', 'm-cr' => 'm-cr', 'maui' => 'maui', 'maxo' => 'maxo', 'midp' => 'midp', 'mits' => 'mits', 'mmef' => 'mmef', 'mobi' => 'mobi', 'mot-' => 'mot-', 'moto' => 'moto',
                'mwbp' => 'mwbp', 'mywa' => 'mywa', 'nec-' => 'nec-', 'newt' => 'newt', 'nok6' => 'nok6', 'noki' => 'noki', 'o2im' => 'o2im', 'opwv' => 'opwv', 'palm' => 'palm', 'pana' => 'pana', 'pant' => 'pant', 'pdxg' => 'pdxg', 'phil' => 'phil', 'play' => 'play', 'pluc' => 'pluc', 'port' => 'port', 'prox' => 'prox',
                'qtek' => 'qtek', 'qwap' => 'qwap', 'rozo' => 'rozo', 'sage' => 'sage', 'sama' => 'sama', 'sams' => 'sams', 'sany' => 'sany', 'sch-' => 'sch-', 'sec-' => 'sec-', 'send' => 'send', 'seri' => 'seri', 'sgh-' => 'sgh-', 'shar' => 'shar', 'sie-' => 'sie-', 'siem' => 'siem', 'smal' => 'smal', 'smar' => 'smar',
                'sony' => 'sony', 'sph-' => 'sph-', 'symb' => 'symb', 't-mo' => 't-mo', 'teli' => 'teli', 'tim-' => 'tim-', 'tosh' => 'tosh', 'treo' => 'treo', 'tsm-' => 'tsm-', 'upg1' => 'upg1', 'upsi' => 'upsi', 'vk-v' => 'vk-v', 'voda' => 'voda', 'vx52' => 'vx52', 'vx53' => 'vx53', 'vx60' => 'vx60', 'vx61' => 'vx61',
                'vx70' => 'vx70', 'vx80' => 'vx80', 'vx81' => 'vx81', 'vx83' => 'vx83', 'vx85' => 'vx85', 'wap-' => 'wap-', 'wapa' => 'wapa', 'wapi' => 'wapi', 'wapp' => 'wapp', 'wapr' => 'wapr', 'webc' => 'webc', 'whit' => 'whit', 'winw' => 'winw', 'wmlb' => 'wmlb', 'xda-' => 'xda-')));
                $isMobile = true;
                break;
            default:
                $isMobile = false;
                break;
        }

        return $isMobile;
    }

    /**
    * Checks if the current user is likely a bot.
    * The user agent string is checked against a list of most common bots and spiders
    * @return bool True when the current user is a bot, false otherwise
    */
    public static final function isABot()
    {
        $botlist = array(
            "acoon",
			"acorn",
			"admantx",
			"adressendeutschland",
			"akula",
			"alexa",
			"amagit",
			"appie",
			"arachnode",
			"asaha",
			"Ask Jeeves",
			"Baiduspider",
			"bingbot",
			"BingPreview",
			"blaiz-bee",
			"bot",
			"ccubee",
			"coccoc",
			"crawler",
			"depspid",
			"egothor",
			"FAST",
			"Feedfetcher-Google",
			"findlinks",
			"Firefly",
			"froogle",
			"girafabot",
			"gonzo",
			"Google Web Preview",
			"Googlebot",
			"heritrix",
			"holmes",
			"ichiro",
			"indexer",
			"InfoSeek",
			"inktomi",
			"l.webis",
			"looksmart",
			"Mediapartners-Google",
			"minirank",
			"mnogosearch",
			"msnbot",
			"NationalDirectory",
			"rabaz",
			"Rankivabot",
			"Scooter",
			"sistrix",
			"Slurp",
			"Sogou web spider",
			"Spade",
			"spider",
			"TechnoratiSnoop",
			"TECNOSEEK",
			"Teoma",
			"textractor",
			"URL_Spider_SQL",
			"watchmouse",
			"WebAlta Crawler",
			"WebBug",
			"WebFindBot",
			"www.galaxy.com",
			"XML Sitemaps Generator",
			"yahoo",
			"yandex",
			"ZyBorg");

        $agent = self::getUserAgent();

        foreach ($botlist as $bot)
        {
            if (stripos($agent, $bot) !== false)
            {
                return true;
            }
        }

        return false;
    }
}
