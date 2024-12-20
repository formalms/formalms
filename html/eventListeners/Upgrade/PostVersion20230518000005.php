<?php
namespace FormaLms\eventListeners\Upgrade;


class PostVersion20230518000005 extends Upgrader{


    public function run() {

        $this->remapSmtpParams();
        $this->remapDomainParams();
        #$this->removeOldInstallationFolders();

    }

    private function remapSmtpParams()
    {
    
        $smtp_group = 14;
        $arrayConfig = [
            'smtp_auto_tls' => 'auto_tls',
            'smtp_debug' => 'debug',
            'smtp_host' => 'host',
            'smtp_port' => 'port',
            'smtp_secure' => 'secure',
            'smtp_user' => 'user',
            'smtp_pwd' => 'password',
            'use_smtp' => 'active',
            'mail_sender' => 'sender_mail_system',
            'mail_sender_name_from' => 'sender_name_system',
            'send_cc_for_system_emails' => 'sender_cc_mails',
            'send_ccn_for_system_emails' => 'sender_ccn_mails',
            'sender_event' => 'sender_mail_notification',
            'use_sender_aclname' => 'sender_name_notification',
            'customer_help_email' => 'helper_desk_mail',
            'customer_help_name_from' => 'helper_desk_name',
            'customer_help_subj_pfx' => 'helper_desk_subject',
            'replyto_mail' => 'reply_to_mail',
            'replyto_name' => 'reply_to_name',
        ];

        $query = 'SELECT DISTINCT param_name, param_value'
                . ' FROM %adm_setting where param_name IN ("' . implode('","', array_keys($arrayConfig)) . '")';
        
            
        $result = sql_query($query);
      
        $res = [];
        if (sql_num_rows($result)) {
            while (list($param_name, $param_value) = sql_fetch_row($result)) {
                if($param_value == 'on' || $param_value == 'off') {
                    $param_value = $param_value == 'on' ? '1' : '0';
                }
                $res[$arrayConfig[$param_name]] = $param_value;
            }

           
            $queryInsert = 'INSERT INTO'
            . ' %adm_mail_configs (title, system) VALUES ("DEFAULT", "1")';

            $result = sql_query($queryInsert);

            $mailConfigId = sql_insert_id();

            foreach($res as $type => $value) {
                $queryInsert = 'INSERT INTO'
                . ' %adm_mail_configs_fields (mailConfigId, type, value) VALUES ("'. $mailConfigId .'", "'. $type .'", "'. $value .'")';
                $result = sql_query($queryInsert);
            }

            $queryDelete = 'DELETE FROM %adm_setting where param_name IN ("' . implode('","', array_keys($arrayConfig)) . '")';
            $result = sql_query($queryDelete);
            return $result;
        } else  {
            return true;
        }

    }

    private function remapDomainParams()
    {

        $query = 'SELECT param_value'
                . ' FROM %adm_setting WHERE param_name = "template_domain"';


        $resultQuery = sql_query($query);

        if (sql_num_rows($resultQuery)) {
            while (list($param_value) = sql_fetch_row($resultQuery)) {
                
                $results = json_decode($param_value);
            }

            if(is_array($results)) {
                foreach($results as $key => $result) {
                    $queryInsert = 'INSERT INTO'
                    . ' %adm_domain_configs (title, domain, template, orgId) VALUES'
                    . '("PROFILE-'.($key+1).'", "'.$result->domain.'","'.$result->template.'","'.$result->node.'")';
    
                    $result = sql_query($queryInsert);
                }
            }
            
          
            $queryDelete = 'DELETE FROM %adm_setting WHERE param_name = "template_domain"';
            $result = sql_query($queryDelete);
            return (bool) $result;
            
        } else {

            //no settings found - nothing to do
                return true;
        }

    }

    private function removeOldInstallationFolders()
    {
        $mainPath = dirname(__DIR__, 2);
        if(is_dir($mainPath . "/install")) {
            recursiveRmdir($mainPath . "/install");
        }

        if(is_dir($mainPath . "/upgrade")) {
            recursiveRmdir($mainPath . "/upgrade");
        }
        

    }

}