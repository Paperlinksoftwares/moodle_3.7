<?php

$sim = similar_text('•	Language barriers.
•	Lack of Context. In a business setting, context is the background, environment or framework surrounding an event or occurrence.
•	Lack of cultural understanding and awareness. 
•	Assumptions - when various factors are thought to be true but are never confirmed
•	Implicit vs explicit communication. Some people are straightforward; others expect you to read between the lines.
•	Non-observance of international protocol and convention
•	Showing or perceiving disrespect
', '•	Language barriers.
•	Lack of Context. In a business setting, context is the background, environment or framework surrounding an event or occurrence.
•	Lack of cultural understanding and awareness. 
•	Assumptions - when various factors are thought to be true but are never confirmed
•	Implicit vs explicit communication. Some people are straightforward; others expect you to read between the lines.
•	Non-observance of international protocol and convention
•	Showing or perceiving disrespect
', $perc);
echo "similarity: $sim ($perc %)\n";
