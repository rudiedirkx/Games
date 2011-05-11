<head>
<script type="text/javascript">
var mydiv;

function init()
{
     mydiv= document.getElementById("myUniqueDiv");
}
function reveal()
{
	alert(mydiv.innerHTML);
}
window.onload=init;
</script>
</head>
<body>

<form id="myForm">
	<input type="button" value="reveal" onclick="reveal();" />
</form>

<div id="myUniqueDiv">foo</div>

</body>