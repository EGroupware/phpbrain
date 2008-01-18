function maketoc() {
	
	// Find the container. If there isn't one, return silently
	var container = document.getElementById('toc');
	if (!container) return;

	// Traverse the document, adding all <h1>..<h6> tags to an array
	var sections = [];
	findSections(document, sections);
	
	// Insert an anchor before the container element so we can link back to it
	var anchor = document.createElement("a"); // Create an <a> mode
	anchor.name = "TOCtop";
	anchor.id = "TOCtop";
	container.parentNode.insertBefore(anchor, container); // add before toc
	
	// Initialize an array that keeps track of section numbers
	var sectionNumbers = [0,0,0,0,0,0];	
	
	// Now loop through the section header elements we found
	for(var s = 0; s < sections.length; s++) {		
		var section = sections[s];
		var sectionNumber = "";
		
		// Figure out what level heading it is
		var level = parseInt(section.tagName.charAt(1));

		if (isNaN(level) || level < 1 || level > 6) continue;
	
		// Increment the section number for this heading level
		// to produce a section number like 2.3.1
		sectionNumbers[level-1]++;
		for(var i = level; i < 6; i++) sectionNumbers[i] = 0;

		// Now combine section numbers for all heading levels
		// to produce a section number like 2.3.1		
		for(i = 0; i < level; i++) {
			sectionNumber += sectionNumbers[i];
			if (i < level-1) sectionNumber += ".";
		}
		
		// Add the section number and a space to the section header title.
		// We place the number in a <span> to make it styleable
		var frag = document.createDocumentFragment(); // to hold span and space
		var span = document.createElement("span");    // span to hold number
		span.className = "TOCSectNum";                // make it styleable
		span.appendChild(document.createTextNode(sectionNumber)); // add sect
		frag.appendChild(span);
		frag.appendChild(document.createTextNode(" ")); // Add the space
		section.insertBefore(frag, section.firstChild); // Add both to header
		
		// Create an anchor to mark the beginning of the section
		var anchor = document.createElement("a");
		anchor.name = "TOC"+sectionNumber; // Name the anchor so we can link
		anchor.id = "TOC"+sectionNumber; // In IE, generated anchors need ids
		
		// Wrap the anchor around the link back to the TOC
		var link = document.createElement("a");
		link.href = "#TOCtop";
		link.className = "TOCBackLink";
		//link.appendChild(document.createTextNode(maketoc.backlinkText));
		link.appendChild(document.createTextNode(document.getElementById('backlinkText').value));
		anchor.appendChild(link);
		
		// Insert the anchor and link immediately before the section header
		section.parentNode.insertBefore(anchor, section);
		
		// Now create a link to this section
		var link = document.createElement("a");
		link.href = "#TOC" + sectionNumber; // Set link destination
		link.innerHTML = section.innerHTML; // Make link text same as heading
		
		// Place the link in a div that is styleable based on the level
		var entry = document.createElement("div");
		entry.className = "TOCEntry TOCLevel" + level; // For CSS Styling
		entry.appendChild(link);
		
		// And add the div to the TOC container
		container.appendChild(entry);
	}
	
	// This metthod recurively traverses the tree rooted at node n, looks
	// for <h1> through <h6> tags, and appends them to the sections array.
	function findSections(n, sects) {
		// Loop through all the cuildren of n
		for(var m = n.firstChild; m != null; m = m.nextSibling) {			
			// Skyp any nodes that are not elements.
			if (m.nodeType != 1 /* Node.Element_NODE */) continue;
			// Skyp the container element since it may have its own heading
			if (m == container) continue;
			if (m.tagName == "P") continue; // Optimization
			
			if (m.tagName.length==2 && m.tagName.charAt(0)=="H") sects.push(m);
			else findSections(m, sects);
		}
	}
}

// Register maketoc() to run automatically whe the document finishes loading
if (window.addEventListener) window.addEventListener("load", maketoc, false);
else if (window.attachEvent) window.attachEvent("onload", maketoc);