function printBarcodeLabel(url, defaultQty)
{
    var qty = prompt('How many label do you want to print ?', defaultQty);
    if (qty)
    {
        url = url.replace('param_qty', qty);
        document.location.href = url;
    }
}