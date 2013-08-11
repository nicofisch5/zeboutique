amCatCopy_Duplicated_Id = 0;
amCatCopy_updateContent_Url = "";

categoryDuplicate = function(url)
{
    location.href = url;
}

processCategoryDuplicate = function()
{
    $('amcatcopy_category_duplicate_form').submit();
}

Event.observe(window, 'load', function()
{
    if (amCatCopy_Duplicated_Id && amCatCopy_updateContent_Url)
    {
        updateContent(amCatCopy_updateContent_Url, {}, true);
    }
});