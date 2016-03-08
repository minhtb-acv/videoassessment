M.mod_videoassessment = {};


M.mod_videoassessment.init_training_change = function(Y) {
    var trainingnode = Y.one('#id_training');
    var video = Y.one('#fitem_id_trainingvideo');
    var point = Y.one('#fitem_id_accepteddifference');
    if (trainingnode) {
        var originalvalue = trainingnode.get('value');
        if (originalvalue != 1) {
        	video.hide();
    		point.hide();
        }
        
        trainingnode.on('change', function() {
        	if (trainingnode.get('value') == 1) {
        		video.show();
        		point.show();
        	} else {
        		video.hide();
        		point.hide();
        	}
        });
    }
};