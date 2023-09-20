<?php
/**
 * @package     JooAg Base
 * @version     1.0.0
 * @for         Joomla 4.x 
 * @author      Joomla Agentur - http://www.joomla-agentur.de
 * @copyright   Copyright (c) 2009 - 2023 Joomla-Agentur All rights reserved.
 * @license     GNU General Public License version 2 or later;
 * @description This Plugin connects to customfields with each other.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filter\InputFilter;


class PlgSystemJoomlaAgency_Base extends CMSPlugin
{
	// This Plugin connects to customfields with each other.
	public function onContentAfterSave($context, $article, $isNew) {
		// Make sure the context is 'com_content.article', to avoid updating other content types
		if ($context !== 'com_content.article') {
			return;
		}

		$childServiceId = $this->params->get('child_service_id');
		$parentTargetId = $this->params->get('parent_target_id');
		
		// Clear previous relations
		$this->removeExistingValues(Factory::getDbo(), $parentTargetId, $article->id);
		$this->removeExistingValues(Factory::getDbo(), $childServiceId, $article->id);

		// Update the fields only if the article ID is available
		if (isset($article->id) && $article->id > 0) {
			$this->updateFields($article->id, $childServiceId, $parentTargetId);
			$this->updateFields($article->id, $parentTargetId, $childServiceId);
		}
	}

    private function updateFields($articleId, $sourceFieldId, $targetFieldId) {
        $db = Factory::getDbo();
        $relatedArticleIds = $this->getRelatedArticleIds($db, $articleId, $sourceFieldId);

        $this->removeExistingValues($db, $targetFieldId, $articleId);

        foreach ($relatedArticleIds as $relatedArticleId) {
            $this->addNewValue($db, $relatedArticleId, $targetFieldId, $articleId);
        }
    }

    private function getRelatedArticleIds($db, $articleId, $fieldId) {
        $query = $db->getQuery(true);
        $query->select($db->quoteName('value'))
            ->from($db->quoteName('#__fields_values'))
            ->where($db->quoteName('item_id') . ' = ' . $articleId)
            ->andWhere($db->quoteName('field_id') . ' = ' . $fieldId);

        $db->setQuery($query);
        return $db->loadColumn();
    }

    private function removeExistingValues($db, $fieldId, $value) {
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__fields_values'))
            ->where($db->quoteName('field_id') . ' = ' . $fieldId)
            ->where($db->quoteName('value') . ' = ' . $value);

        $db->setQuery($query);
        $db->execute();
    }

    private function addNewValue($db, $itemId, $fieldId, $value) {
        $query = $db->getQuery(true);
        $columns = ['item_id', 'field_id', 'value'];
        $values = [$itemId, $fieldId, $db->quote($value)];

        $query->insert($db->quoteName('#__fields_values'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        $db->execute();
    }
	
    public function onAfterRoute()
    {
        $app = JFactory::getApplication();

        if ($app->isClient('administrator')) {
            $option = $app->input->getCmd('option', '');
            $view   = $app->input->getCmd('view', '');
            $layout = $app->input->getCmd('layout', '');

            if ($option === 'com_content' && $view === 'article' && $layout === 'edit') {
                $this->updateSubformFile();
            }
        }
    }

    private function updateSubformFile()
    {
        //Änder eine Joomla Core Datei damit Subform Fields immer horizontal ausgegeben werden.
		$subformPath = JPATH_ROOT . '/plugins/fields/subform/subform.php';

        if (file_exists($subformPath)) {
            $fileContent = file_get_contents($subformPath);
            $searchString = 'if (count($subfields) >= 5) {';
            $replaceString = 'if (count($subfields) >= 0) {';
            $newContent = str_replace($searchString, $replaceString, $fileContent);

            if ($newContent !== $fileContent) {
                file_put_contents($subformPath, $newContent);
            }
        }
    }
	protected $app;
	
	public function onBeforeRender()
	{
		// Fügen Sie Ihre CSS-Datei hinzu
        if (Factory::getApplication()->isClient('administrator'))
        {
			Factory::getDocument()->addStyleSheet(JUri::root() . '/plugins/system/joomlaagency_base/assets/css/j4-backend-changes.css');
			Factory::getDocument()->addScript(JUri::root() . '/plugins/system/joomlaagency_base/assets/js/j4-change-backend-labels.js');
        }
		
		
		//Ausblenden einiger Felder im Backend
		if ($this->app->isClient('administrator') && $this->app->input->get('option') === 'com_content' && $this->app->input->get('view') === 'article') {
			$fieldsToHide = [
				'#jform_version_note-lbl',
				'#jform_version_note',
				'#jform_note',
				'#jform_note-lbl',
			];

			$jsHideCode = '';
			foreach ($fieldsToHide as $field) {
				$jsHideCode .= "field = document.querySelector('{$field}'); if (field) { field.parentElement.style.display = 'none'; }";
			}

			$this->app->getDocument()->addScriptDeclaration("
				(function() {
					document.addEventListener('DOMContentLoaded', function() {
						let field;
						{$jsHideCode}
					});
				})();
			");
		}
		
		// Packt ein Info Panel inst Backend von Joomla
        // Überprüfen, ob wir uns im Backend befinden


$url = Uri::getInstance()->toString();

// URL bereinigen
$filter = new InputFilter();
$cleanUrl = $filter->clean($url, 'STRING');

// Basis-Domain aus der bereinigten URL extrahieren
$parsedUrl = parse_url($cleanUrl);
$host = $parsedUrl['host'] ?? '';

$hostParts = explode('.', $host);
if (count($hostParts) >= 2) {
    $baseDomain = $hostParts[count($hostParts) - 2] . '.' . $hostParts[count($hostParts) - 1];
} else {
    $baseDomain = $host; // Falls es nur einen Teil gibt (selten, aber möglich)
}
        if (JFactory::getApplication()->isClient('administrator')) {
            $document = JFactory::getDocument();
            $buffer = $document->getBuffer('component');

            // Überprüfen, ob der Buffer-Inhalt vorhanden ist und die gewünschte Klasse enthält
            if ($buffer && strpos($buffer, 'class="card-columns"') !== false) {
                $newHtml = '<div class="col-md-12 module-wrapper" style="grid-row-end: span 19;"><div class="card mb-3 "><div class="col d-flex align-items-start p-3">
	<div class="flex-shrink-0 me-3">
	<img style="width:120px;" src="'. JUri::root() . '/plugins/system/joomlaagency_base/assets/images/joomla-agentur-logo-quadrat-black_v2.svg" alt="Joomla Agentur Logo">
	</div>
	<div class="vr me-3"></div>
	<div>
		<p>
			<strong>Joomla-Agentur.de</strong>: Betreuung und Support!<br>
			Zögern Sie nicht bei Fragen und Problemen uns zu Kontaktieren.<br>
			Support-Formular: <a target="_blank" href="https://joomla-agentur.de/support-formular?form[Webseite]='.$host.'" >Bei Problemen</a><br>
			Tel.: <a href="tel:00494064831872" >+49 (0) 40 6483 1872</a><br>
			E-Mail: <a href="mailto:info@joomla-agentur.de" >info@joomla-agentur.de</a><br>
			Web: <a target="_blank" href="https://joomla-agentur.de" >joomla-agentur.de</a><br>
			
		</p>
	</div>
	</div>
</div>
</div>'; // Ersetzen Sie dies durch Ihr benutzerdefiniertes HTML
                $buffer = str_replace('<div class="card-columns">', '<div class="card-columns">' . $newHtml, $buffer);
                $document->setBuffer($buffer, 'component');
            }
        }

	}
	
	//ersetzt strings in Joomla
    public function onAfterRender()
    {
        $app = JFactory::getApplication();

        // Nur im Frontend ausführen
        if ($app->isClient('site')) {
            $body = $app->getBody();
            $replacements = $this->params->get('replacements', array());

            foreach ($replacements as $replacement) {
                $search = isset($replacement->search) ? $replacement->search : '';
                $replace = isset($replacement->replace) ? $replacement->replace : '';

                if (!empty($search) && !empty($replace)) {
                    $body = str_replace($search, $replace, $body);
                }
            }

            $app->setBody($body);
        }
		
		// Nur im Backend und in der Artikelansicht ausführen
		// Generieren des JavaScript-Codes basierend auf den XML-Parametern
		$app = JFactory::getApplication();
		$body = $app->getBody();

		$backendLabelReplacements = $this->params->get('backendlabelreplacements', array());

		foreach ($backendLabelReplacements as $repItem) {
			$customFieldParent = isset($repItem->customfieldparent) ? $repItem->customfieldparent : '';
			$customFieldSubform = isset($repItem->customfieldsubform) ? $repItem->customfieldsubform : '';
			$replace = isset($repItem->replace) ? $repItem->replace : '';

			// Erstellen der ID basierend auf den Variablen
			$labelId = "jform_com_fields__" . $customFieldParent . "__field" . $customFieldSubform . "-lbl";
			

			$part1 = '/<label id=\"jform_com_fields__';
			$part2 = $customFieldParent . '__field' . $customFieldSubform . '-lbl\"';
			$part3 = '[^>]*>([^<]+)<\/label>/i';
			$mypattern = $part1 . $part2 . $part3;

			// Ersetzen des Inhalts des Labels
			$newLabel = '<label id="' . $labelId . '" for="jform_com_fields_' . $customFieldParent . '_field' . $customFieldSubform . '">' . $replace . '</label>';
			
			$body = preg_replace($mypattern, $newLabel, $body);

		}

		$app->setBody($body);
		return true;
    }

}