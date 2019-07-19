<?php
/**
 * @package EDK
 */

$modInfo['srp_status']['name'] = "SRP status";
$modInfo['srp_status']['abstract'] = "Adds a SRP request link to the kill detail page.";

event::register('killDetail_context_assembling', 'srp_status::addMenu');

class srp_status
{
    public static function addMenu( $pkd )
    {
        $pkd->addBefore( 'menuSetup', 'srp_status::menuSetup' );
    }

    /**
     * Add a menu item to the kill detail page.
     * @param pKillDetail $pkd
     */
    public static function menuSetup($pkd)
    {
        $kill = $pkd->getKill(); 

        // SRP for owner alliances/corps victims only
        if ( array_search($kill->getVictimAllianceID(), config::get('cfg_allianceid')) === false and
             array_search($kill->getVictimCorpID(),     config::get('cfg_corpid'))     === false )
        {
//            $pkd->addMenuItem("link", "not applicable");
            return;
        }

        $pkd->addMenuItem("caption", "SRP status");

        // SRP service URL check
        $url = config::get('srp_status_url');
        if ( is_null($url) or
            filter_var($url, FILTER_VALIDATE_URL) === false ) 
        {
            $pkd->addMenuItem("link", "invalid config");
            return;
        }

        $par = array (
               "action"  => "getstatus",
               "killid"  => str_replace('&amp;', '&', edkURI::page('kill_detail', $kill->getID(), "kll_id")),
               "victim"  => $kill->getVictimName(),
               "ship"    => $kill->getVictimShip()->getName(),
               "loss"    => $kill->getISKLoss(),
               "tstamp"  => $kill->getTimeStamp()
        );

        $ch = curl_init();
//        if ($self->config->getSSLVerification() == false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        }
        $redirs = config::get('srp_status_redirs');
        if ( isset( $redirs) and $redirs > 0 )
        {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $redirs);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($par));
        $status = json_decode(curl_exec($ch));
        $err = curl_error($ch);
        curl_close($ch);

        if ( isset($status) and is_string($status->status) and 
             (is_null($status->url) or filter_var($status->url, FILTER_VALIDATE_URL)) )
            $pkd->addMenuItem("link", $status->status, $status->url);
        else
            $pkd->addMenuItem("link", "service failure");
    }

}
