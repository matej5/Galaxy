function move() {
    var desc = document.getElementById('description');
    if(screen.width >= 600) {
        if (desc.style.right != '-45%') {
            desc.style.right = '-45%';
        } else {
            desc.style.right = '20px';
        }
    } else {
        if (desc.style.top != '80%') {
            desc.style.top = '80%';
        } else {
            desc.style.top = '15%';
        }
    }
}