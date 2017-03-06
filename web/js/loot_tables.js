var lootObj = {};
var hasError = false;
var importingProcess = false;
var importErrorList = [];

//Componenent
var templates = document.getElementById("templates");
var content = document.getElementById("content");
var pools = document.getElementById("pools");
var output = document.getElementById("output");
var importError = document.getElementById("error");

//Options
var prettyPrintChecbox = document.getElementById("prettyPrint");
var input = document.getElementById("table_input");

function addTemplate(node, clazz){
    var selectedNode = node.getElementsByClassName(clazz)[0];
    appendTemplate(selectedNode, clazz + "-elem");
    rebuildOutput();
}

function changeSelect(node){
    if(node.hasAttribute("update")){
        var clazz = node.getAttribute("update");
        var selectedNode = node.parentElement.getElementsByClassName(clazz)[0];
        emptyNode(selectedNode);
        appendTemplate(selectedNode, node.value);
    }
    rebuildOutput();
}


function emptyNode(node){
    var lc;
    while(lc = node.lastChild){
        node.removeChild(lc);
    }
}

// BEGIN CHANGE CARRADE.EU
function getJSONTable(){
    hasError = false;
    lootObj = buildObject(content);

    if (hasError) return false;
    else return lootObj;
}
// END CHANGE CARRADE.EU

function rebuildOutput(){
    if(importingProcess){
        return;
    }
    hasError = false;
    lootObj = buildObject(content);

    if(hasError){
        setNodeError(output, "This table contains errors, fix them before copying this.");
    }else{
        clearNodeError(output);
    }

    if(prettyPrintChecbox.checked){
        output.value = JSON.stringify(lootObj, null, 4);
    }else{
        output.value = JSON.stringify(lootObj);
    }
}

function buildObject(mainNode){
    var obj = {}
    for(var i in mainNode.childNodes){
        var node = mainNode.childNodes[i];
        if(node.nodeType == 1){
            if(node.classList.contains("warp-skip")){
                node = node.getElementsByClassName("warp")[0];
                if(node == undefined){
                    continue;
                }
            }else if(node.classList.contains("nest-obj")){
                var ob = buildObject(node);
                for(var k in ob){
                    obj[k] = ob[k];
                }
                continue;
            }
            if(node.classList.contains("json-elem")){
                clearNodeError(node);
                var key;
                if(node.hasAttribute("set-key")){
                    key = node.getElementsByClassName(node.getAttribute("set-key"))[0].value;
                }else{
                    key = node.getAttribute("key-name");
                }
                var value = getValueFromNode(node);
                if(value != null){
                    var defaut = node.getAttribute("data-default");
                    if(defaut != null){
                        if(defaut != value.toString()){
                            obj[key] = value;
                        }
                    }else{
                        obj[key] = value;
                    }
                }
            }
        }
    }
    return obj;
}

function buildArray(mainNode){
    var array = [];
    for(var i in mainNode.childNodes){
        var node = mainNode.childNodes[i];
        if(node.nodeType == 1){
            if(node.classList.contains("warp-skip")){
                node = node.getElementsByClassName("warp")[0];
                if(node == undefined){
                    continue;
                }
            }else if(node.classList.contains("nest-obj")){
                var ob = buildObject(node);
                for(var k in ob){
                    array.push(ob[k]);
                }
                continue;
            }
            if(node.classList.contains("json-elem")){
                clearNodeError(node);
                var value = getValueFromNode(node);
                if(value != null){
                    array.push(value);
                }
            }
        }
    }
    return array;
}


function getValueFromNode(node){
    clearNodeError(node);
    var dataType = node.getAttribute("elem-type");
    if(dataType == "obj"){
        var obj = buildObject(node);
        if(!isEmptyObject(obj)){
           return obj;
        }
    }else if(dataType == "range"){
        var obj = buildObject(node);
        if(obj != null){
            if(obj.min == obj.max){
                return obj.min;
            }else{
                if(obj.min > obj.max){
                    setNodeError(node, obj.min + " must be lower than " + obj.max);
                }else{
                    return obj;
                }
            }
        }
    }else if(dataType == "array"){
        var array = buildArray(node);
        if(!isEmptyArray(array)){
            return array;
        }
    }else if(dataType == "array-const"){
        var array = buildArray(node);
        if(!isEmptyArray(array)){
            if(node.hasAttribute("singleton")){
                if(array.length == 1){
                    return array[0];
                }
            }
            return array;
        }

    }else if(dataType == "num"){
        if(isNaN(Number(node.value))){
            setNodeError(node, "\"" + node.value + "\" is not a number");
        }else{
            return parseInt(node.value);
        }
    }else if(dataType == "float"){
        if(isNaN(Number(node.value))){
            setNodeError(node, "\"" + node.value + "\" is not a number");
        }else{
            return parseFloat(node.value);
        }
    }else if(dataType == "string"){
        return node.value;
    }else if(dataType == "id"){
        var id = parseItemID(node.value);
        if(id == null && node.value != ""){
            setNodeError(node, "\"" + node.value + "\" is not a correct item id");
        }else{
            return id;
        }
    }else if(dataType == "bool"){
        return node.checked;
    }else if(dataType == "select"){
        if(needInit(node)){
            changeSelect(node);
        }
        var selectedValue = node.options[node.selectedIndex].value;
        if(selectedValue != "none"){
            return selectedValue;
        }
    }else if(dataType == "key"){
        return getValueFromNode(node.getElementsByClassName("wrapper")[0]);
    }else{
        addImportError("Unknown elem-type value : " + dataType);
    }
    return null;
}

function needInit(node){
    if(node.classList.contains("toInit")){
        node.classList.remove("toInit");
        return true;
    }
    return false;
}


function setNodeError(node, message){
    hasError = true;
    node.classList.add("data-error");
    node.setAttribute("title", message);
}

function clearNodeError(node){
    node.classList.remove("data-error");
    node.removeAttribute("title");
}

function isEmptyObject(o){
    for(var p in o){
        if (o.hasOwnProperty(p)){
            return false;
        }
    }
    return true;
}

function isEmptyArray(array){
    return array.length == 0;
}

function parseItemID(id){
    if(id.indexOf(":") > 0){
        return id;
    }
    return "minecraft:" + id;
}

function removeNode(node){
    node.parentElement.parentElement.removeChild(node.parentElement);
    rebuildOutput();
}

// BEGIN CHANGE CARRADE.EU
function promptImport(){
    //var imported = input.value;//prompt("Enter a loot table");
    importFromJSON(input.value);
}

function importFromJSON(json_string){
    if(json_string != null && json_string.length > 0){
        importErrorList = [];
        var parsed;
        try{
            parsed = JSON.parse(json_string);
        }catch(error){
            addImportError("Unable to parse JSON : " + error);
        }
        if(parsed != null){
            importingProcess = true;
            emptyNode(pools);
            importObject(content, parsed);
            importingProcess = false;
            rebuildOutput();
            input.value = "";
        }
        importError.value = "";
        if(importErrorList.length > 0){
            for(var i in importErrorList){
                importError.value += importErrorList[i] + "\n";
            }
            importError.classList.remove("hidden");
        }else{
            importError.classList.add("hidden");
        }
    }
}
// END CHANGE CARRADE.EU

function setValueFromType(node, obj){
     if(node.classList.contains("nest-obj")){
        for(var n in node.childNodes){
            if(node.childNodes[n] != undefined && node.childNodes[n].nodeType == 1 && node.childNodes[n].classList.contains("json-elem")){
                setValueFromType(node.childNodes[n], obj);
                return;
            }
        }
        addImportError("Unable to find correct nesting node");
        return
    }

    var dataType = node.getAttribute("elem-type");
    if(dataType == "array"){
        var temp = node.getAttribute("template");
        for(var i in obj){
            addTemplate(node.parentElement, temp);
            importObject(node.lastChild, obj[i]);
        }
    }else if(dataType == "obj"){
        if(node.hasAttribute("template")){
            var temp = node.getAttribute("template");
            for(var i in obj){
                addTemplate(node.parentElement, temp);
                var ob = {}
                ob[i] = obj[i];
                importObject(node.lastChild, ob);
            }
        }else{
            for(var i in obj){
                importObject(node, obj);
            }
        }
    }else if(dataType == "array-const"){
        var temp = node.getAttribute("template");
        if(node.hasAttribute("singleton")){
            if(typeof(obj) == "string"){
                obj = [obj]
            }
        }
        emptyNode(node)
        for(var i in obj){
            addTemplate(node.parentElement, temp);
            setValueFromType(node.lastChild, obj[i]);
        }

    }else if(dataType == "range"){
        if(typeof(obj) == "object"){
            importObject(node, obj);
        }else{
            importObject(node, {min:obj,max:obj});
        }
    }else if(dataType == "num"){
        node.value = obj;
    }else if(dataType == "float"){
        node.value = obj;
    }else if(dataType == "id"){
        if(obj.startsWith("minecraft:") > 0){
            node.value = obj.split(":")[1];
        }else{
            node.value = obj
        }
    }else if(dataType == "bool"){
        node.checked = obj;
    }else if(dataType == "string"){
        node.value = obj;
    }else if(dataType == "select"){
        var matchingSelect = false;
        needInit(node);
        if(obj.startsWith("minecraft:") > 0){
            obj = obj.split(":")[1];
        }
        for(var i in node.options){
            if(node.options[i].value == obj){
                node.selectedIndex = i;
                matchingSelect = true;
                break;
            }
        }
        if(!matchingSelect){
            if(obj.indexOf(":") > 0){
                addImportError("Skipping custom element : " + obj)
            }else{
                addImportError("Unknown element : " + obj);
            }
        }else{
            changeSelect(node);
        }
    }else{
        addImportError("Unknown elem-type value : " + dataType);
    }
}

function importObject(node, obj){
    if(node.classList.contains("nest-obj")){
        for(var n in node.childNodes){
            if(node.childNodes[n] != undefined && node.childNodes[n].nodeType == 1){
                importObject(node.childNodes[n], obj);
            }
        }
        return;
    }
    for(var key in obj){
        var child = getNodeWithAttribute(node, key);
        if(child !=  null){
            setValueFromType(child, obj[key])
        }else{
            addImportError("No node found for key : " + key);
        }
    }
}

function addImportError(msg){
    importErrorList.push(msg);
}

function getNodeWithAttribute(node, key){
    if(node.hasAttribute("set-key")){
        var k = node.getAttribute("set-key");
        var wrap = node.getElementsByClassName(k)[0];
        wrap.value = key;
        return node.getElementsByClassName("wrapper")[0];;
    }
    var child;
    for(var n in node.childNodes){
        child = node.childNodes[n];
        if(child.nodeType == 1){
            if(child.classList.contains("nest-obj")){
                for(var n in child.childNodes){
                    if(child.childNodes[n] != undefined && child.childNodes[n].nodeType == 1){
                        var nestedNode = getNodeWithAttribute(child.childNodes[n], key);
                        if(nestedNode != null){
                            return nestedNode;
                        }
                    }
                }
            }
            if(child.classList.contains("warp-skip")){
                child = child.getElementsByClassName("warp")[0];
                if(child == undefined){
                    continue;
                }
            }
            if(child.getAttribute("key-name") == key){
                return child;
            }
        }
    }
    return null;
}


function appendTemplate(node, templateName){
    var clazzMatch = templates.getElementsByClassName("tpl-" + templateName);
    if(clazzMatch.length == 0){
        addImportError("Cannot find template : tpl-"  + templateName);
        return;
    }
    var template = clazzMatch[0].cloneNode(true);
    template.classList.remove("tpl-" + templateName);
    template.classList.add(templateName)
    node.appendChild(template);
}

function focusArea(area){
    area.focus();
    area.select();
}

content.addEventListener("change", function(){
    rebuildOutput();
});

rebuildOutput();
