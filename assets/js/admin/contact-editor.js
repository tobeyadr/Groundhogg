var ContactEditor = {};

(function ($, editor) {

    $.extend(editor, {

        init: function () {

            $('#meta-table').click(function (e) {
                if ($(e.target).closest('.deletemeta').length) {
                    $(e.target).closest('tr').remove();
                }
            });

            $('.addmeta').click(function () {

                var $newMeta = "<tr>" +
                    "<th>" +
                    "<input type='text' class='input' name='newmetakey[]' placeholder='" + $('.metakeyplaceholder').text() + "'>" +
                    "</th>" +
                    "<td>" +
                    "<input type='text' class='regular-text' name='newmetavalue[]' placeholder='" + $('.metavalueplaceholder').text() + "'>" +
                    " <span class=\"row-actions\"><span class=\"delete\"><a style=\"text-decoration: none\" href=\"javascript:void(0)\" class=\"deletemeta\"><span class=\"dashicons dashicons-trash\"></span></a></span></span>\n" +
                    "</td>" +
                    "</tr>";
                $('#meta-table').find('tbody').prepend($newMeta);

            });

            $('.create-user-account').click(function () {
                $('#create-user-form').submit();
            });


            $('.nav-tab').click(function (e) {

                var $tab = $(this);

                $('.nav-tab').removeClass('nav-tab-active');
                $tab.addClass('nav-tab-active');

                $('.tab-content-wrapper').addClass('hidden');
                $('#' + $tab.attr('id') + '_content').removeClass('hidden');

                $('#active-tab').val($tab.attr('id').replace('tab_', ''));
                document.cookie = "gh_contact_tab=" + $tab.attr('id') + ";path=/;";

            });

            $(".edit-notes").click(function (e) {

                $(".display-notes").show();

                $(".edit-note-module").html("");

                note = $(e.target).closest(".gh-notes-container").find(".display-notes");

                note_module = $(e.target).closest(".gh-notes-container").find(".edit-note-module");


                note.hide();

                var note_text = note.text().replace("\n", "").replace(/\s{2,}/g, " ").trim()

                note_module.html(
                    "<p>" +
                    "<textarea class=\"new-note\" name=\"add_note\" id=\"_note\" cols=\"64\" rows=\"3\"> "+ note_text +" </textarea>" +
                    "</p>" +
                    "<p>" +
                    "<input type=\"button\" id=\"save-notes\" value = \"save\" class=\"button save-notes\"/>" +
                    "<span id=\"delete-link\" class='cancel-notes'><a class=\"delete\"\n" + "href=\"javascript:void (0)\">Cancel</a></span>" +
                    "</p>");


                $(".save-notes").click(function (event) {
                    adminAjaxRequest(
                        {
                            action: "groundhogg_edit_notes",
                            note: $(e.target).closest(".gh-notes-container").find(".new-note").val(),
                            note_id: note.data("note-id") ,
                        },
                        function callback(response) {

                            // Handler
                            if (response.success) {
                                note.text(response.data.note);
                                $(e.target).closest(".notes-time-right").find(".note-date").text(response.data.date_text);
                                note_module.html("");
                                note.show();

                            } else {
                                alert(response.data);
                            }
                        }
                    );
                });
                $(".cancel-notes").click(function (event) {
                    note_module.html("");
                    note.show();

                });
            });

            $(".delete-note").click(function (e) {
                if (confirm("Are you sure you want to delete this note?")) {
                    note = $(e.target).closest(".gh-notes-container").find(".display-notes");
                    adminAjaxRequest(
                        {
                            action: "groundhogg_delete_notes",
                            note_id: note.data("note-id") ,
                        },
                        function callback(response) {
                            // Handler
                            if (response.success) {

                                $(e.target).closest(".gh-notes-container").replaceWith("");
                                alert( response.data.msg);

                            } else {
                                alert(response.data);
                            }
                        }
                    );
                }
            });

        }
    });

    $(function () {
        editor.init();
    });

})(jQuery, ContactEditor);