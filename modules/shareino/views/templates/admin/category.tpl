<div class="alert alert-dismissible" id="syncMessageBox" role="alert" hidden>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
    <p id="syncMessageText"></p>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="panel panel-body col-sm-12">
            <div class="row">
                <div class="col-sm-12" style="direction: rtl">
                    <h2 class="text-center" style="padding: 10px;">
                        کالاهای دسته‌بندی که انتخاب کرده‌اید به سایت دکمه ارسال خواهند شد.
                    </h2>
                </div>
            </div>
            <div class="row">
                <form method='post' id='saveCategoryForm' action='{$url}' data-token='{$token}'>
                    <div class="col-sm-12">
                        {$storeCategoryBox}
                    </div>
                    <div class="col-sm-12" style="direction: rtl">
                        <button type="button" class="btn btn-default" name="dokme_save_category"
                                id="saveCategory">
                            <span class="glyphicon glyphicon-send" aria-hidden="true" style="display: inline;"></span>
                            ذخیره
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var messageBox = $("#syncMessageBox");
        var messageText = $("#syncMessageText");
        var token = $('#saveCategoryForm').attr('data-token');
        var selectedCategory = {$category};

        $(selectedCategory).each(function (i) {
            $('input[type=checkbox]').each(function () {
                if (($(this).val() == selectedCategory[i])) {
                    $(this).attr('checked', 'checked');
                }
            });

        });

        $('#saveCategory').on('click', function () {
            var categories = [];
            jQuery('input[type=checkbox]:checked').each(function (i) {
                categories[i] = jQuery(this).val();
            });

            selectedCategories(categories);
        });

        function selectedCategories(categories) {
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'ajax-tab.php',
                data: {
                    ajax: true,
                    token: token,
                    categories: categories,
                    action: 'SelectedCategories',
                    controller: 'AdminManageCats'
                }
            }).done(function (data) {
                console.log(data);
                if (data.status) {
                    message(true, data.message);
                } else {
                    message(false, data.message);
                }
            }).fail(function () {

            });
        }

        function message(status, message) {
            messageText.html(message);
            messageBox.show(500);
            messageBox.addClass(status ? 'alert-success' : 'alert-danger');
        }
    });
</script>
