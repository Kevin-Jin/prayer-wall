var TRIGGER_ZONE = 100; //in pixels from bottom of the window

var loading = false;
var end = true;
var loadCount = 1;

function loadMore() {
	loading = true;
	$('#loading').css('display', 'block');
	$.ajax({
		type: $('#nextpageform').attr('method'),
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

			$('.board').isotope('appended', newlyLoaded);
		}
	});
}

function enableConfirmLeave() {
	$(window).bind('beforeunload', function() {
		if ($('#newmessage').val().trim().length !== 0 || $('#newtitle').val().trim().length !== 0)
			return 'Your post has not yet been submitted. The draft will be discarded.';
	});
}

function disableConfirmLeave() {
	$(window).unbind('beforeunload');
}

//begin code for centered masonry
$.Isotope.prototype._getCenteredMasonryColumns = function() {
	this.width = this.element.width();

	var parentWidth = this.element.parent().width();
	this.masonry.columnWidth =
			this.options.masonry && this.options.masonry.columnWidth ||
			// or use the size of the first item
			this.$filteredAtoms.outerWidth(true) ||
			// if there's no items, use size of container
			parentWidth;
	this.masonry.cols = Math.max(Math.floor(parentWidth / this.masonry.columnWidth), 1);
};

$.Isotope.prototype._masonryReset = function() {
	//layout-specific props
	this.masonry = {};
	//FIXME shouldn't have to call this again
	this._getCenteredMasonryColumns();
	this.masonry.colYs = [];
	for (var i = this.masonry.cols - 1; i >= 0; --i)
		this.masonry.colYs.push(0);
};

$.Isotope.prototype._masonryResizeChanged = function() {
	var prevColCount = this.masonry.cols;
	//recalculate cols
	this._getCenteredMasonryColumns();
	return this.masonry.cols !== prevColCount;
};

$.Isotope.prototype._masonryGetContainerSize = function() {
	var unusedCols = 0;
	//count unused columns
	for (var i = this.masonry.cols - 1; i >= 0 && this.masonry.colYs[i] === 0; --i)
		unusedCols++;

	return {
		height : Math.max.apply(Math, this.masonry.colYs),
		//fit container to columns that have been used;
		width : (this.masonry.cols - unusedCols) * this.masonry.columnWidth
	};
};
//end code for centered masonry

$(document).ready(function() {
	if ($('#nextpageform').length > 0) {
		end = false;
		$('input#nextpage').remove();
		$('<input type="hidden" name="scroll" value="1">').appendTo($('#nextpageform div'));
	} else {
		end = true;
	}
	$('.board').append($('<p id="loading" style="background-color: white; position: fixed; right: 20px; bottom: 20px; display: none; z-index: 1; font-size: 20pt">Loading more posts...</p>'));

	$('body').append('<div id="dimmer"></div>').keyup(function(e) {
		if ($('#dimmer').css('display') !== 'none' && e.keyCode === 27) {
			$('#newmessage').val($('#newmessage').val().trim()).trigger('autosize.resize');;
			$('#dimmer').fadeOut(100);
			$('.board').isotope('reLayout');
			$('textarea, input').blur();
		}
	});
	$('#dimmer').click(function() {
		$('#newmessage').val($('#newmessage').val().trim()).trigger('autosize.resize');;
		$('#dimmer').fadeOut(100);
		$('.board').isotope('reLayout');
	});
	$('#composecontainer').css('z-index', '3').append($('<input type="hidden" name="echo" value="1">')).click(function() {
		$('#dimmer').fadeIn(200);
	});
	$('#newtitle').keyup(function() {
		if ($(this).val().trim().length === 0) {
			if ($('#newmessage').val().trim().length === 0)
				disableConfirmLeave();
		} else {
			enableConfirmLeave();
		}
	});
	$('#newmessage').removeAttr('rows').autosize().keypress(function() {
		return ($(this).val().trim().length < 21845);
	}).keyup(function() {
		if ($(this).val().trim().length === 0) {
			$('#makepost').attr('disabled', 'disabled').attr('title', 'You must type a message body.');
			if ($('#newtitle').val().trim().length === 0)
				disableConfirmLeave();
		} else {
			$('#makepost').removeAttr('disabled').removeAttr('title');
			enableConfirmLeave();
		}
	});
	$('#makepost').attr('type', 'button').attr('disabled', 'disabled').attr('title', 'You must type a message body.').click(function() {
		$('#compose input[type=text], #compose textarea').attr('readonly', 'readonly');
		$(this).attr('disabled', 'disabled');
		var originalButtonText = $(this).val();
		$(this).val('Submitting...');
		$.ajax({
			type: $('#compose').attr('method'),
			url: $('#compose').attr('action'),
			data: $('#compose').serialize(),
			success: function(data) {
				$('#dimmer').fadeOut(100);
				$('#compose input[type=text], #compose textarea').removeAttr('readonly').val('');
				$('#newmessage').trigger('autosize.resize');
				$('#makepost').val(originalButtonText).attr('title', 'You must type a message body.');

				var newlyLoaded = $(data);
				$('#compose').after(newlyLoaded);
				$('.board').isotope('appended', newlyLoaded).isotope('reLayout');
			}
		});
	});

	$('.board').isotope({
		itemSelector: '.note',
		animationEngine: 'jquery',
		animationOptions: { duration: 500 },
		onLayout: function() {
			if (loading) {
				loadCount++;
				$('#loading').css('display', 'none');
				loading = false;
			}
			//after initial load or window resize or content load completion,
			//make sure we actually are able to scroll down.
			//if not, just keep loading more content until we are.
			if (!end && ($(window).scrollTop() + TRIGGER_ZONE >= $(document).height() - $(window).height()))
				loadMore();
		}
	});
});
$(window).scroll(function() {
	if (!end && !loading && $(window).scrollTop() + TRIGGER_ZONE >= $(document).height() - $(window).height())
		loadMore();
});