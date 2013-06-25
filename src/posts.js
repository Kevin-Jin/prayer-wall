TRIGGER_ZONE = 100; //in pixels from bottom of the window

var triggered = false;
var end = true;
var loadCount = 1;

function loadMore() {
	triggered = true;
	$.ajax({
		type: "GET",
		url: $('#nextpageform').attr('action'),
		data: $('#nextpageform').serialize(),
		success: function(data) {
			var newlyLoaded = $('<div id="scrollLoaded' + loadCount + '"></div>').append(data);
			$('.board').append(newlyLoaded);

			var newStartInput = newlyLoaded.find('input#start');
			if (parseInt(newStartInput.attr('value')) === -1) {
				end = true;
				$('#nextpageform').remove();
				newStartInput.remove();
			} else {
				$('#nextpageform input#start').replaceWith(newStartInput);
			}
			loadCount++;
			triggered = false;

			$('.board').masonry('appended', newlyLoaded).masonry('reloadItems');
			documentOrWindowHeightChanged();
		}
	});
}

function documentOrWindowHeightChanged() {
	//TODO: this needs to be done AFTER Masonry reflows everything!
	if (!end && !triggered && $(window).height() >= $(document).height())
		loadMore();
}

function pagePositionChanged() {
	if (!end && !triggered && $(window).scrollTop() + TRIGGER_ZONE >= $(document).height() - $(window).height())
		loadMore();
}

$(document).ready(function() {
	$('.board').masonry({
		itemSelector: '.note'
	});

	if ($('#nextpageform').length > 0) {
		end = false;
		$('input#nextpage').remove();
		$('<input type="hidden" name="scroll" value="1">').appendTo($('#nextpageform div'));
	} else {
		end = true;
	}

	documentOrWindowHeightChanged();
});
$(window).scroll(pagePositionChanged).resize(documentOrWindowHeightChanged);