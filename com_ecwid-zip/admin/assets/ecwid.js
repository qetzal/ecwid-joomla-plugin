/**
 * @version   1.3 July 15, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

window.addEvent('domready', function(){
	var rockettheme = $$('#rockettheme a');
	if (rockettheme.length && rockettheme[0]){
		rockettheme.addEvents({
			'mousedown': function() { this.removeClass('normal').removeClass('mouseup').addClass('mousedown'); },
			'mouseup': function() { this.removeClass('normal').removeClass('mousedown').addClass('mouseup'); },
			'mouseenter': function() { this.removeClass('normal').removeClass('mousedown').addClass('mouseup'); },
			'mouseleave': function() { this.removeClass('mousedown').removeClass('mouseup').addClass('normal'); }
		});
	}
});