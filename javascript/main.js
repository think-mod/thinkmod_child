// Put link from breadcrumb on title.
let courseTitle = document.getElementsByClassName("page-header-headings")[0].firstChild;
let linkToCourse;
let tempLink;

if (courseTitle) {
    let breadcrumbs = document.getElementsByClassName("breadcrumb-item");
    for (let i = 0; i < breadcrumbs.length; i++) {
        tempLink = breadcrumbs[i].firstChild.href;
        if(tempLink.includes("/course/view.php")) {
            linkToCourse = tempLink
        }
        if(tempLink.includes("#section")) {
            linkToCourse = tempLink
        }
    }
}

if (linkToCourse) {
    let linkText = courseTitle.innerText
    courseTitle.innerText = ""
    courseTitle.innerHTML = '<a href="' + linkToCourse + '">' + linkText + '</a>';
}