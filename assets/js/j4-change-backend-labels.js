/**
Field3 = Subform - Upload1
Field1 = Subform - Text1
Field2 = Subform - Text2
Field8 = Subform - Text3
field38 = Subform - Text4
Field23 = Subform - Text5
Field24 = Subform - Text6
Field25 = Subform - Text7
Field26 = Subform - Text8
Field9 = Subform - Textarea
Field38 = Subform - Articles
Field36 = Subform - Articles Parent Services
Field37 = Subform - Articles Child Services
Field 12 = Disabled Enabled
Field 21 = Editor
**/
document.addEventListener("DOMContentLoaded", function () {

  // Mapping für die Labels
  const labels = {
	//Appetizer
    'menu': {
	'1': 'Titel',
	'2': 'Meta',
    },
	'hero': {
      '1': 'Titel',
      '2': 'Meta',
    },
	'teaser': {
	'1': 'Titel',
	'2': 'Meta',
	'7': 'Linked',
	'70': 'No',
	'71': 'Yes',
    },
	'problems': {
	'1': 'Alternativer Titel',
	'37': 'Assignment - Problems',
    },
	//Image
    'icon': {
      '12': 'Icon Upload',
      '1': 'Icon Alt',
      '3': 'Icon Source',
      '4': 'Icon Autor',
      '14': 'Icon License'
    },
    'image': {
      '12': 'Image Upload',
      '1': 'Image Alt',
      '3': 'Image Source',
      '4': 'Image Autor',
      '14': 'Image License'
    },
    'background_image': {
      '12': 'Background Image Upload',
      '1': 'Background Image Alt',
      '3': 'Background Image Source',
      '4': 'Background Image Autor',
      '14': 'Background Image License'
    },
	
	
	'inclusive_services': {
      '38': 'Inklusive Leistungen auswhälen'
    },
	'optional_services': {
      '38': 'Optionale Services auswählen'
    },
	'contact_person': {
      '38': 'Person auswählen'
    },
	'agency_benefits': {
      '1': 'Titel',
      '2': 'Meta',
      '38': 'Zuordnungen'
    },
	'benefits_headline': {
      '1': 'Titel',
      '2': 'Meta',
    },
	'call_to_action': {
      '1': 'Titel',
      '2': 'Meta',
    },
	'faq_headline': {
      '1': 'Titel',
      '2': 'Meta',
    },
	'snippets': {
      '1': 'Titel',
      '2': 'Meta',
      '8': 'Content',
      '3': 'Bild',
      '10': 'Icon',
      '23': 'Link',
      '24': 'Link Text',
    },

	'formular_headline': {
      '1': 'Titel',
      '2': 'Meta',
      '42': 'Akivierte Fragen'
    },
	'child_services': {
      '1': 'Titel',
      '2': 'Meta',
      '12': 'Linked 0-No | 1-Yes'
    }
  };

// Funktion zum Setzen der Label-Inhalte
function setLabelContent(prefix, id, content) {
	let element = document.getElementById(`jform_com_fields__${prefix}__field${id}-lbl`);
	if (!element) {
		// Wenn das Label-Element nicht gefunden wird, suchen Sie nach dem Radio-Button-Label
		element = document.querySelector(`label[for="jform_com_fields__${prefix}__field${id}"]`);
	}
	if (element) {
		element.innerHTML = content;
	}

}

// Setze die Label Inhalte
for (const [prefix, labelSet] of Object.entries(labels)) {
	for (const [id, content] of Object.entries(labelSet)) {
		setLabelContent(prefix, id, content);
	}
}

});
