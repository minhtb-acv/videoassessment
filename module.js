if (!M.mod_videoassessment) {
    M.mod_videoassessment = {
        /**
         * @memberOf M.mod_videoassessment
         * @param {YUI} Y
         * @param {Number} cmid
         */
        main_init: function(Y, cmid) {
            this.Y = Y;
            this.cmid = cmid;

            this.init_video_preview();
        },

        /**
         * @param {YUI} Y
         * @param {Object} users
         * @param {Object} assocs
         */
        videos_init: function(Y, users, assocs) {
            this.Y = Y;
            this.users = users;
            this.assocs = assocs;

            this.assocpanel = new Y.Panel({
                srcNode: "#assocpanel",
                width: 450,
                height: 420,
                centered: true,
                modal: false,
                render: true,
                visible: false
            });
            this.assocpanel.plug(Y.Plugin.Drag);
            this.assocpanel.dd.addHandle(".yui3-widget-hd");

            this.init_video_preview();

            Y.on("click", function(e) {
                if (!confirm(M.str.videoassessment.reallydeletevideo)) {
                    e.preventDefault();
                }
            }, ".videodel");
        },

        peers_init: function(Y) {
        },

        peers_confirm_random: function() {
            return confirm(M.str.videoassessment.reallyresetallpeers);
        },

        assess_init: function(Y) {
        },

        init_video_links: function(Y, cmid) {
            this.Y = Y;
            this.cmid = cmid;

            this.init_video_preview();

            Y.on("click", function(e) {
                e.preventDefault();
                var videoid;
                if (!(videoid = e.target.getData("videoid"))) {
                    videoid = e.target.get("parentNode").getData("videoid");
                }
                if (videoid) {
                    this.videos_show_video_preview(videoid);
                } else {
                    Y.log("ビデオID取れず");
                }
            }, ".videolink", this);
        },

        init_video_preview: function() {
            var Y = this.Y;

            this.videopreviewpanel = new Y.Panel({
                srcNode: "#videopreview",
                width: 450,
                height: 420,
                zIndex: 1000,
                centered: true,
                modal: true,
                render: true,
                visible: false
            });

            this.videopreviewpanel.on("visibleChange", function(e) {
                if (e.newVal) {
                    Y.one("#videopreview").show();
                } else {
                    Y.one("#videopreview").hide();
                    this.videopreviewpanel.set("bodyContent", "");
                }
            }, this);
        },

        /**
         *
         * @param {YUI} Y
         * @param {Node} div
         */
        videos_update_assoc_cell: function(Y, div) {
            var html = "";

            html += '<select size=4></select>';

            html += "<select>";
            for (var i in this.users) {
                html += "<option>"+this.users[i].fullname+"</option>";
            }
            html += '</select>';
            div.setContent(html);
        },

        /**
         *
         * @param {Number} videoid
         */
        videos_show_video_preview: function(videoid) {
            var src = "videopreview.php?id="+this.cmid+"&amp;videoid="+videoid+"&amp;width=400&amp;height=300";
            var html = '<iframe class="videopreview" width="420" height="370" src="'+src+'"></iframe>';

            this.videopreviewpanel.set("bodyContent", html);
            this.videopreviewpanel.show();
        },
        
        videos_show_video_training_preview: function(videoid) {
            var src = "videopreview.php?id="+this.cmid+"&amp;videoid="+videoid+"&amp;width=400&amp;height=300";
            var html = '<iframe class="videopreview" width="420" height="370" src="'+src+'"></iframe>';

            this.videopreviewpanel.set("bodyContent", html);
            this.videopreviewpanel.set("visible", true);
            this.videopreviewpanel.focus();
        },

        videos_show_video_preview_by_user: function(userid, timing) {
            var src = "videopreview.php?id="+this.cmid+"&amp;userid="+userid+"&amp;timing="+timing+"&amp;width=400&amp;height=300";
            var html = '<iframe class="videopreview" width="420" height="370" src="'+src+'"></iframe>';

            this.videopreviewpanel.set("bodyContent", html);
            this.videopreviewpanel.set("visible", true);
            this.videopreviewpanel.focus();
        },

        /**
         *
         * @param {Number} videoid
         */
        videos_show_assoc_panel: function(videoid) {
            this.videoid = videoid;
            this.videos_assoc_panel_refresh();

            this.assocpanel.set("visible", true);
            this.assocpanel.focus();
        },

        videos_assoc_panel_refresh: function() {
            var Y = this.Y;

            var filter = "unassociated";
            if (Y.one("#studentfilter")) {
                filter = Y.one("#studentfilter").get("value");
            }

            with (Y.Node) {
                var o = create("<div></div>");
                o.append(M.str.videoassessment.liststudents
                        +' <select id="studentfilter" onchange="M.mod_videoassessment.videos_assoc_panel_refresh()">\
                        <option value="unassociated">'+M.str.videoassessment.unassociated+'</option>\
                        <option value="associated">'+M.str.videoassessment.associated+'</option>\
                        <option value="all">'+M.str.moodle.all+'</option>\
                </select>');

                o.append('<div>Before/after: '
                        +'<input type="radio" name="timing" id="timingbefore" checked="checked" onclick="M.mod_videoassessment.set_timing(\'before\')"> <label for="timingbefore">'+M.str.videoassessment.before+'</label>'
                        +' <input type="radio" name="timing" id="timingafter" onclick="M.mod_videoassessment.set_timing(\'after\')"> <label for="timingafter">'+M.str.videoassessment.after+'</label></div>');

                var rowclass = 0;
                var list = create('<div class="associatepanel-studentlist"></div>');
                for (var i in this.users) {
                    var user = this.users[i];
                    var associated = (Y.Array.indexOf(user.assocvideos, this.videoid) != -1);

                    if (filter == "all"
                        || filter == "unassociated" && user.assocvideos == 0
                        || filter == "associated" && user.assocvideos != 0) {
                        var row = create('<div></div>');
                        row.setAttribute("class", "videoassoc-studentlist r"+rowclass);
                        rowclass ^= 1;
                        var check = create('<label><input type="checkbox" class="assocuser" value="'+i+'"/>'+user.fullname+'</label>');
                        if (associated) {
                            check.one("input").set("checked", true);
                        }
                        row.append(user.userpicture).append(check);
                        list.append(row);
                    }
                }
                o.append(list);

                o.append('<input type="button" value="'+M.str.videoassessment.saveassociations+'" onclick="M.mod_videoassessment.videos_save_associations()"/>');
            }

            Y.one("#id_videoid").set("value", this.videoid);

            this.assocpanel.set("bodyContent", o);

            Y.one("#studentfilter").set("value", filter);
        },

        videos_save_associations: function() {
            var Y = this.Y;
            var assocdata = [];
            Y.all(".assocuser").each(function(e) {
                assocdata.push([e.get("value"), e.get("checked")]);
            });
            Y.one("#id_assocdata").set("value", Y.JSON.stringify(assocdata));
            Y.one("#mform1").invoke("submit");
        },

        /**
         *
         * @param {String} value
         */
        set_timing: function(value) {
            var Y = this.Y;
            Y.one("#id_timing").set("value", value);
            Y.log("timing set "+value);
        },

        /**
         *
         * @param {YUI} Y
         */
        report_combine_rubrics: function(Y) {
            Y.all(".report-rubrics").each(function(node) {

                Y.Array.each(["before", "after"], function(timing) {
                    var teacherrubric = node.one("#rubrics-"+timing+"teacher");
                    if (!teacherrubric) {
                        return;
                    }

                    teacherrubric.all(".remark").each(function(node) {
                        node.addClass("rubrictext-teacher");
                    });

                    node.one("#heading-"+timing+"teacher").setStyle("display", "none");

                    /* MinhTB VERSION 2 */
                    var classTextRubric = 0; // Le Xuan Anh Ver2
                    var totalClassRubric = 0; // Le Xuan Anh Ver2
                    var totalRubric = 0; // Le Xuan Anh Ver2
                    var classInsert = ''; // Le Xuan Anh Ver2

                    totalRubric = node.one("#rubrics-beforeself").all(".criterion").size() +
                                  node.one("#rubrics-beforepeer").all(".criterion").size() +
                                  node.one("#rubrics-beforeclass").all(".criterion").size(); // Le Xuan Anh Ver2

                    Y.Array.each(["self", "peer", "class"], function(gradertype) {
                    /* END */
                        node.one("#rubrics-"+timing+gradertype).all(".criterion").each(function(crit) {
                            classTextRubric++; // Le Xuan Anh Ver2
                            var critname = crit.one(".description").getHTML();
                            var levelname = crit.one(".checked .definition").getHTML();
                            var remark = crit.one(".remark").getHTML();

                            var critfound = false;
                            teacherrubric.all(".criterion").each(function(tcrit) {
                                if (critfound) {
                                    return;
                                }
                                if (tcrit.one(".description").getHTML() == critname) {
                                    critfound = true;
                                    var levelfound = false;
                                    tcrit.all(".level").each(function(level) {
                                        if (levelfound) {
                                            return;
                                        }
                                        if (level.one(".definition").getHTML() == levelname) {
                                            levelfound = true;

                                            // Le Xuan Anh Ver2
                                            if (gradertype == 'class') {
                                                totalClassRubric++;
                                                classInsert = '<span class="inferiorlevelmarker rubrictext-' + gradertype + '">'
                                                    + M.str.videoassessment[gradertype] + '(' + totalClassRubric + ')</span>';
                                            } else {
                                                level.one(".level-wrapper").insert('<span class="inferiorlevelmarker rubrictext-'+gradertype+'">'
                                                    +M.str.videoassessment[gradertype]+'</span>');
                                            }

                                            if (classTextRubric == totalRubric) {
                                                level.one(".level-wrapper").insert(classInsert);
                                            }
                                            // End

                                        }
                                    });
                                    tcrit.one(".remark").insert('<div class="rubrictext-'+gradertype+'">'+remark+'</span>');
                                    crit.setStyle("display", "none");
                                    crit.addClass("hidden-information"); /* Xuan Anh : Use when print report */
                                }
                            });
                        });
                        /* Xuan Anh : Use when print report */
                        var criterion = node.one("#rubrics-"+timing+gradertype).all(".criterion").size();
                        var hiddenCriterion = node.one("#rubrics-"+timing+gradertype).all(".hidden-information").size();
                        if ("rubrics-beforeclass" != "rubrics-"+timing+gradertype) {
                            if (!node.one("#rubrics-"+timing+gradertype).one(".comment") && criterion <= hiddenCriterion) {
                                node.one("#rubrics-"+timing+gradertype).setStyle("display", "none");
                            }

                            if (node.one("#rubrics-"+timing+gradertype).one(".comment") && criterion <= hiddenCriterion) {
                                node.one("#rubrics-"+timing+gradertype+" .pagebreak").remove();
                            }
                        } else {
                            if (criterion <= hiddenCriterion) {
                                node.one("#rubrics-beforeclass").all('.pagebreak').each(function(pagebreak){
                                    pagebreak.remove();
                                });
                                node.one("#rubrics-beforeclass").previous().previous().all('.pagebreak').each(function (pagebreak) {
                                    pagebreak.remove();
                                });
                            }
                        }
                        node.one("#rubrics-beforetraining").all('.pagebreak').each(function (pagebreak) {
                            pagebreak.remove();
                        });
                        /* /Xuan Anh : Use when print report */

                        node.one("#heading-"+timing+gradertype).setStyle("display", "none");
                    });
                });
            });
        },

        init_print: function(Y) {
        	print();
        },

        init_publish_videos: function(Y) {
        	this.Y = Y;

        	this.init_check_all("#all-video-check", ".video-check");
        },

        init_delete_videos: function(Y) {
        	this.Y = Y;

        	this.init_check_all("#all-video-check", ".video-check");

        	Y.on("click", function(e) {
        		var count = Y.all(".video-check:checked").size();

        		if (count == 0) {
        			alert(M.str.mod_videoassessment.errorcheckvideostodelete);
        			e.preventDefault();
        			return;
        		}

        		if (!confirm(M.util.get_string("confirmdeletevideos", "mod_videoassessment", count))) {
        			e.preventDefault();
        		}
        	}, "#id_submitbutton");
        },

        init_check_all: function(all, checkboxes) {
        	var Y = this.Y;

        	Y.on("click", function(e) {
        		Y.all(checkboxes).set("checked", e.target.get("checked"));
        	}, all);
        },

        manage_grades_init: function(Y) {
        	Y.on("click", function(e) {
        		if (!confirm(M.str.videoassessment.confirmdeletegrade)) {
        			e.preventDefault();
        		}
        	}, ".deletegrade");
        }
    };
}
