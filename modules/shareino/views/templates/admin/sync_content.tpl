<div class="panel col-xs-12">
    <div class="panel-heading">
        عملیات
    </div>
    <div class="panel-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12">

                    <form class="" action="{$actionUrl}" method="post">

                        <button type="submit" class="btn btn-default" name="shareino_synchronize_all">
                            همسان سازی همه
                        </button>
                        <a href="{$url|escape:'htmlall':'UTF-8'}" class="btn btn-default"><span
                                    class="glyphicon glyphicon-cog"></span>
                            تنظیمات ماژول</a>


                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
{if isset($list)}
    {$list}
{/if}
