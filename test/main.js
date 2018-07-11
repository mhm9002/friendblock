function addTag() {

    var x = document.createElement("tr");
    var inner = "<td>" + document.getElementById("salma").value + "</td><td>" + document.getElementById("zaina").value + "</td><td>" + document.getElementById("ghadeer").value + "</td>";
    x.innerHTML = inner;
    var tag = document.getElementById("najjar").appendChild(x);
    //tag.innerHTML=x;

    document.getElementById("salma").value = "";
    document.getElementById("zaina").value = "";
    document.getElementById("ghadeer").value = "";

}
