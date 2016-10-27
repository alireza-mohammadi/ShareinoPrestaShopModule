{*
 * 2015-2016 Shareino
 *
 * NOTICE OF LICENSE
 *
 * This source file is for module that make sync Product With shareino server
 * https://github.com/SaeedDarvish/PrestaShopShareinoModule
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Shareino to newer
 * versions in the future. If you wish to customize Shareino for your
 * needs please refer to https://github.com/SaeedDarvish/PrestaShopShareinoModule for more information.
 *
 * @author    Saeed Darvish <sd.saeed.darvish@gmail.com>
* @copyright 2015-2016 Shareino Co
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  Tejarat Ejtemaie Eram
*}

<div class="panel col-xs-12">
    <div class="panel-heading">
        Organize Categories
    </div>
    <div class="panel-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12">
                    <form class="form-horizontal" action="{$url}" method="post">

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">Store Categorey :</label>
                            <div class="col-sm-10">
                                <select class="dropdown store_categories cats_title  js-states form-control"
                                        style="width:100%;" name="store_cat">
                                    {foreach key=cid item=cat from=$categories}
                                        <option value="{$cat.id_category}">
                                            {$cat.name}
                                        </option>
                                        {foreachelse}
                                        <option value="-1">could'nt load any things</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Shareino Categoreis</label>
                            <div class="col-sm-10">
                                <select class="dropdown shareino_categories js-states  form-control" multiple="multiple"
                                        name="shareino_cats[]"
                                        style="width:100%;">
                                    {foreach key=cid item=cat from=$shareinoCategories}
                                        <option class="cats_title" value="{$cid}">
                                            {$cat|replace:'--':'&nbsp;'}
                                        </option>
                                        {foreachelse}
                                        <option value="-1">could'nt load any things</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-default" name="organize_categories_submit">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{if isset($list)}
    {$list}
{/if}

<script type="application/javascript">
    $(".store_categories").select2({
        placeholder: "Select a Category",
        allowClear: true
    });

    $(".shareino_categories").select2({
        placeholder: "Select a Category"
    });
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 text-center">
            <img src="{$module_dir}shareino/views/img/logo.png" alt="" title="" style="margin-bottom: 20px;"/>
        </div>
    </div>
</div>

<style>
    .cats_title {
        font-size: 14px;
        font-family: Tahoma, Arial, Helvetica, sans-serif;
    }
</style>
