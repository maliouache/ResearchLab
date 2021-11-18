function decodeHtml(){
    var di=document.getElementById("work_div");
    var si=document.getElementById("work_div_hidden");
    di.innerHTML=si.textContent;
    console.log('done');
}


function decodeHtml_add(){
    var co=document.getElementById("ips_symfonyprojectbundle_work_cONTENT");
    var di=document.getElementById("work_div");
    di.innerHTML=co.value;

    decodeHtml(co.value);
}
// addEventListener("DOMContentLoaded", function(event){
//    console.log('loaded'); 
// });