function moveLeft(n,a)
{
    $.get('move.php?n='+n+'&t=-1', function ()
    {
        console.log(a);
        $(a).closest('div').insertBefore($(a).closest('div').prev());
    });

    return false;
};

function moveRight(n,a)
{
    $.get('move.php?n='+n+'&t=+1', function ()
    {
        $(a).closest('div').next().insertBefore($(a).closest('div'));
    });

    return false;
}