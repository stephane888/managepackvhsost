(function(Drupal) {
	Drupal.behaviors.managepackvhsost = {
		attach: function(context, settings) {
			if (context.querySelector) {
				const buttons = context.querySelectorAll('.wix-theme-price .nav-tabs button');
				console.log('buttons : ', buttons)
				if (buttons) {
					const applySelectType = (type) => {
						if (context.getElementById) {
							const selectType = context.getElementById('managepackvhsost__type_pack');
							if (selectType) {
								selectType.value = type;
							}
						}
					}
					buttons.forEach((button) => {
						button.addEventListener('click', (even) => {
							applySelectType(button.getAttribute('id'));
						})
					})
				}

			}
		},
	};
})(window.Drupal);