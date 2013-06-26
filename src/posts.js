var TRIGGER_ZONE = 100; //in pixels from bottom of the window

var loading = false;
var end = true;
var loadCount = 1;

function loadMore() {
	loading = true;
	$('#loading').css('display', 'block');
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

			$('.board').isotope('appended', newlyLoaded);
		}
	});
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