<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright Neurall
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */
require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use NBLabox\Box\NumericableBox;
use NBLabox\Curl\CurlSession;

class NBLabox extends eqLogic
{
    /*     * *************************Attributs****************************** */

    public static $_widgetPossibility = array('custom' => true);

    /*     * ***********************Methode static*************************** */

     /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */
    public static function cronDayly() {
        foreach (self::byType('NBLabox') as $neurall) {
            if ($neurall->getIsEnable() == 1)
            if ($neurall->getConfiguration('laboxAddr') != '') {
                log::add('NBLabox', 'debug', 'Pull CronDayly pour neurall api');
                $neurall->updateInfo();
                $neurall->toHtml('dashboard');
                $neurall->toHtml('mobile');
                $neurall->refreshWidget();
            }
            }
    }

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */
    public static function cronHourly() {
        foreach (self::byType('NBLabox') as $neurall) {
            if ($neurall->getIsEnable() == 1)
            if ($neurall->getConfiguration('laboxAddr') != '') {
                log::add('NBLabox', 'debug', 'Pull CronHourly pour neurall api');
                $neurall->updateInfo();
                $neurall->toHtml('dashboard');
                $neurall->toHtml('mobile');
                $neurall->refreshWidget();
            }
            }
    }


    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
     */
    public static function cron($_eqlogic_id = null) {
//        if ($_eqlogic_id !== null) {
//            $eqLogics = array(eqLogic::byId($_eqlogic_id));
//        } else {
//            $eqLogics = eqLogic::byType('NBLabox');
//        }
//        foreach ($eqLogics as $t) {
//            if ($t->getIsEnable() == 1) {
//            if ($t->getConfiguration('laboxAddr') != '') {
//                log::add('NBLabox', 'debug', 'Pull Cron pour neurall api');
//                $t->updateInfo();
//                $t->toHtml('dashboard');
//                $t->toHtml('mobile');
//                $t->refreshWidget();
//            }
//            }
//        }
        return;
    }

    /** Restart the box. */
    public function restartModem()
    {
        try {
            log::add('NBLabox', 'debug', __METHOD__ . " restart modem started");

            $ipBox = $this->getConfiguration('laboxAddr');
            $login = $this->getConfiguration('laboxLogin');
            $password = $this->getConfiguration('laboxPassword');
            log::add('NBLabox', 'debug', __METHOD__ . " restart : addr found is [" . $ipBox . ']');
            if (($ipBox == "") || ($login == "") || ($password == "")) {
                return;
            }

            $box = new NumericableBox($ipBox, new CurlSession());
            $box->login($login, $password);
            $box->restart();

            log::add('NBLabox', 'debug', __METHOD__ . ' restart done');

        } catch (Exception $e) {
            log::add('NBLabox', 'debug', __METHOD__ . " restart failed " . $e->getMessage());
        }
    }

    /** Updates plugin's commands value. */
    public function updateInfo()
    {
        try {
            log::add('NBLabox', 'debug', __METHOD__ . " get status called");

            $ipBox = $this->getConfiguration('laboxAddr');
            log::add('NBLabox', 'debug', __METHOD__ . " addr found is [" . $ipBox . ']');

            if ($ipBox == "") {
                return;
            }

            $cmd = $this->getCmd(null, 'laboxip');
            $previousIp = $cmd->execCmd(); // récupère l'adresse ip précédente

            $box = new NumericableBox($ipBox, new CurlSession());

            $currentIp = $box->getPublicIp();

            $this->checkAndUpdateCmd('laboxip', $currentIp);
            $this->checkAndUpdateCmd('laboxgw', $box->getGateway());
            $this->checkAndUpdateCmd('laboxhwver', $box->getHardwareVersion());
            $this->checkAndUpdateCmd('laboxswver', $box->getSoftwareVersion());
            $this->checkAndUpdateCmd('laboxmask', $box->getNetworkMask());
            $this->checkAndUpdateCmd('laboxdownload', $box->getDownloadBandwidth());
            $this->checkAndUpdateCmd('laboxupload', $box->getUploadBandwidth());

            $dns = $box->getDns();
            if (count($dns) >= 1) {
                $this->checkAndUpdateCmd('laboxdns1', $dns[0]);
            }
            if (count($dns) >= 2) {
                $this->checkAndUpdateCmd('laboxdns2', $dns[1]);
            }

            $this->checkAndUpdateCmd('laboxetat', 'normal');

            $this->checkAndUpdateCmd('laboxprevip', $previousIp);
            if ($previousIp != $currentIp) {
                log::add('NBLabox', 'info', "l'adresse ip publique de la box a changé ($currentIp)");

            }

            log::add('NBLabox', 'debug', __METHOD__ . ' update done');

        } catch (Exception $e) {
            log::add('NBLabox', 'debug', __METHOD__ . " update info failed " . $e->getMessage());
            $this->checkAndUpdateCmd('laboxetat', 'ko');
        }
    }


   /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        log::add('NBLabox', 'debug', __METHOD__ . " equipment name=" . $this->getName() . " laboxAddr=" . $this->getConfiguration('laboxAddr')
                . " laboxLogin=" . $this->getConfiguration('laboxLogin')
                . " laboxPassword=" . $this->getConfiguration('laboxPassword'));
    }

    private function createCmd($cmdid, $cmdlabel, $visible = 1) {
        $config = $this->getCmd(null, $cmdid);
        if (!is_object($config)) {
            log::add('NBLabox', 'debug', __METHOD__ . " creation de ".$cmdid);
            $config = new NBLaboxCmd();
            $config->setLogicalId($cmdid);
            $config->setIsVisible($visible);
            $config->setIsHistorized(0);
            $config->setName($cmdlabel);
            $config->setType('info');
            $config->setSubType('string');
            $config->setEventOnly(1);
            $config->setEqLogic_id($this->getId());
            // 05/09/2016 ajout compatibilité avec appli mobile
            $config->setDisplay('generic_type','GENERIC').
            $config->save();
        } else
            log::add('NBLabox', 'debug', __METHOD__ . " ".$cmdid." already exists");
    }

    public function postInsert() {

        log::add('NBLabox', 'debug', __METHOD__ . " equipment name=" . $this->getName() . " laboxAddr=" . $this->getConfiguration('laboxAddr')
                . " laboxLogin=" . $this->getConfiguration('laboxLogin')
                . " laboxPassword=" . $this->getConfiguration('laboxPassword'));

        $this->createCmd('laboxetat', 'Etat Labox');
        $this->createCmd('laboxip', 'IP Labox');
        $this->createCmd('laboxmask', 'Masque Labox');
        $this->createCmd('laboxgw', 'Passerelle Labox');
        $this->createCmd('laboxhwver', 'Version HW Labox');
        $this->createCmd('laboxswver', 'Version SW Labox');
        $this->createCmd('laboxprevip', 'IP publique precedente',0);
        $this->createCmd('laboxdns1', 'DNS primaire');
        $this->createCmd('laboxdns2', 'DNS secondaire');
        $this->createCmd('laboxdownload', 'Debit');
        $this->createCmd('laboxupload', 'Debit montant');

        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new NBLaboxCmd();
            $refresh->setName(__('Rafraichir', __FILE__));
        }
// bouton refresh
        $refresh->setEqLogic_id($this->getId());
        $refresh->setLogicalId('refresh');
        $refresh->setType('action');
        $refresh->setSubType('other');
        // 05/09/2016 ajout compatibilité avec appli mobile
        $refresh->setDisplay('generic_type','DONT').
        $refresh->setOrder(98);
        $refresh->save();
// bouton reboot
        $reboot = $this->getCmd(null, 'reboot');
        if (!is_object($reboot)) {
            $reboot = new NBLaboxCmd();
            $reboot->setName(__('Redemarrer', __FILE__));
        }
        $reboot->setEqLogic_id($this->getId());
        $reboot->setLogicalId('reboot');
        $reboot->setType('action');
        $reboot->setSubType('other');
        // 05/09/2016 ajout compatibilité avec appli mobile
        $reboot->setDisplay('generic_type','DONT').
        $reboot->setOrder(99);
        $reboot->save();
// display mode
        $display= $this->getCmd('info', 'isDisplay');
        if (!is_object($display)) {
            $display = new NBLaboxCmd();
            $display->setName(__('isDisplay', __FILE__));
        }
        $display->setEqLogic_id($this->getId());
        $display->setLogicalId('isDisplay');
        $display->setType('info');
        $display->setSubType('string');
        // 05/09/2016 ajout compatibilité avec appli mobile
        $display->setDisplay('generic_type','DONT').
        $display->setOrder(97);
        $display->save();
        }

    public function preSave() {
     }

    public function postSave() {
        log::add('NBLabox', 'debug', __METHOD__ . " equipment name=" . $this->getName() . " laboxAddr=" . $this->getConfiguration('laboxAddr')
                . " laboxLogin=" . $this->getConfiguration('laboxLogin')
                . " laboxPassword=" . $this->getConfiguration('laboxPassword'));
        if (!$this->getId()) // un appel sans ID sort immédiatement sans mise à jour
            return;
        $this->updateInfo();
        // return;
        $this->toHtml('dashboard');
        $this->toHtml('mobile');
        $this->refreshWidget();
        $display= $this->getCmd('info', 'isDisplay');
        $displayFlag = $this->getConfiguration("isDisplay", "1");
        log::add('NBLabox', 'debug', __METHOD__ . " display flag " . $displayFlag);
        $display->setValue($displayFlag);
        $display->save();
        $display->event($displayFlag);
        log::add('NBLabox', 'debug', __METHOD__ . " refresh widget done " . $this->getName());
    }

    public function preUpdate() {
        log::add('NBLabox', 'debug', __METHOD__ . " equipment name=" . $this->getName() . " laboxAddr=" . $this->getConfiguration('laboxAddr')
                . " laboxLogin=" . $this->getConfiguration('laboxLogin')
                . " laboxPassword=" . $this->getConfiguration('laboxPassword'));
        if ($this->getConfiguration('laboxAddr') == '')
            throw new Exception(__("L'adresse de la box est vide, entrez l'adresse IP de la box", __FILE__));
        if ($this->getConfiguration('laboxLogin') == '')
            throw new Exception(__("Le nom de connexion est vide, entrez le compte d'administration de la box", __FILE__));
        if ($this->getConfiguration('laboxPassword') == '')
            throw new Exception(__("Le mot de passe est vide, entrez le mot de passe du compte d'administration de la box", __FILE__));
    }

    public function postUpdate() {
        log::add('NBLabox', 'debug', __METHOD__ . " enter postupdate " . $this->getName());
    }

    public function preRemove() {
        log::add('NBLabox', 'debug', __METHOD__ . " equipment name=" . $this->getName() . " laboxAddr=" . $this->getConfiguration('laboxAddr')
                . " laboxLogin=" . $this->getConfiguration('laboxLogin')
                . " laboxPassword=" . $this->getConfiguration('laboxPassword'));
    }

    public function postRemove() {
        log::add('NBLabox', 'debug', __METHOD__ . " equipment name=" . $this->getName() . " laboxAddr=" . $this->getConfiguration('laboxAddr')
                . " laboxLogin=" . $this->getConfiguration('laboxLogin')
                . " laboxPassword=" . $this->getConfiguration('laboxPassword'));
    }

    /**
     * Non obligatoire mais permet de modifier l'affichage du widget.
     * {@inheritDoc}
     */
    public function toHtml($version = 'dashboard')
    {
        log::add('NBLabox', 'debug', __METHOD__ . " update widget code for " . $version);
        $version = jeedom::versionAlias($version);
        $replace = $this->preToHtml($version);
        if (!is_array($replace)) {
            return $replace;
        }

        $replace['#laboxAddr#'] = $this->getConfiguration('laboxAddr');
        $replace['#laboxIP#'] = $this->getConfiguration('laboxIP');

        foreach ($this->getCmd() as $cmd) {
            if ($cmd->getType() == 'info') {
                $value = $cmd->execCmd();
                log::add('NBLabox', 'debug', __METHOD__ . " iterate for id=" . $cmd->getLogicalId() . " name=" . $cmd->getName() . " val=" . $value);
                $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
                $replace['#' . $cmd->getLogicalId() . '#'] = $value;
                $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
                $replace['#' . $cmd->getLogicalId() . '_collectDate#'] = $cmd->getCollectDate();
                if ($cmd->getIsHistorized() == 1) {
                    $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
                }
            } else {
                $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            }
        }

        $html = template_replace($replace, getTemplate('core', $version, 'eqlogic', 'NBLabox'));
        $html = $this->postToHtml($version, $html);
        return $html;
    }
}

class NBLaboxCmd extends cmd
{
    /** {@inheritDoc} */
    public function execute($options = array())
    {
        $cmd = $this->getLogicalId();
        log::add('NBLabox', 'debug', __METHOD__ . " entered, running cmd=" . $cmd);
        switch ($cmd) {
            case "refresh":
                /** @var NBLabox $equipment */
                $equipment = $this->getEqLogic();
                $equipment->updateInfo();
                $equipment->refreshWidget();
                break;
            case 'reboot':
                /** @var NBLabox $eqLogic */
                $equipment = $this->getEqLogic();
                $equipment->restartModem();
                break;

        }
    }
}
