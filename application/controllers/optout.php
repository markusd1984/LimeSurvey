<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * optout
 *
 * @package LimeSurvey
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class optout extends LSYii_Controller {

    function __construct()
    {
        parent::__construct();
    }

    function local($langcode, $surveyid, $token)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');
        $sLanguageCode = $langcode;
        $iSurveyID = $urveyid;
        $sToken = $token;
        $sToken = sanitize_token($sToken);

        if (!$iSurveyID)
        {        
            $this->redirect($this->getController()->createUrl('/'));
        }
        $iSurveyID = (int)$iSurveyID;
        //Check that there is a SID
        // Get passed language from form, so that we dont loose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode)
        {
            $baselang = GetBaseLanguageFromSurveyID($iSurveyID);
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang(array('langcode' => $baselang));
        }
        else
        {
            $sLanguageCode = sanitize_languagecode($sLanguageCode);
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang(array('langcode' => $sLanguageCode));
            $baselang = $sLanguageCode;
        }
        $thissurvey=getSurveyInfo($iSurveyID,$baselang);

        if ($thissurvey==false || !tableExists("tokens_{$iSurveyID}")){
            $html = $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $row = Tokens_dynamic::getEmailStatus($iSurveyID, $sToken);

            if ($row == false)
            {
                $html = $clang->gT('You are not a participant in this survey.');
            }
            else
            {
                $usresult = $row['emailstatus'];
                if ($usresult == 'OK')
                {
                    $usresult = Tokens_dynamic::updateEmailStatus($iSurveyID, $sToken, 'OptOut');
                    $html = $clang->gT('You have been successfully removed from this survey.');
                }
                else
                {
                    $html = clang->gT('You have been already removed from this survey.');
                }
            }            
        }

        //PRINT COMPLETED PAGE
        if (!$thissurvey['templatedir'])
        {
            $thistpl=sGetTemplatePath($defaulttemplate);
        }
        else
        {
            $thistpl=sGetTemplatePath($thissurvey['templatedir']);
        }

        $this->_renderHtml($html,$thistpl);
    }

    private function _renderHtml($html,$thistpl)
    {
        sendcacheheaders();
        doHeader();
        $data['html'] = $html;
        $data['thistpl'] = $thistpl;
        $this->getController()->render('/opt_view',$data);
        doFooter();
    }

}
