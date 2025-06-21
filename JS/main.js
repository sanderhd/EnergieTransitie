let editMode = false;
let draggedWidget = null;

function handleDrop(event) {
  event.preventDefault();
  if (!editMode) return;
  
  const widgetName = event.dataTransfer.getData("text/plain");
  if (widgetName) {
    loadWidget(widgetName);
  }
}

function saveDashboardState() {
  const widgets = [];
  document.querySelectorAll('#dashboard .widget').forEach(widgetElement => {
    const widgetData = {
      name: widgetElement.dataset.widgetName, // We moeten deze data-attribute toevoegen
      html: widgetElement.innerHTML, // Sla de innerHTML op (inclusief unieke ID's en scripts)
      style: widgetElement.getAttribute('style') || '', // Sla inline styles op (voor grootte en positie)
      id: widgetElement.id // Sla de unieke ID van de widget container op
    };
    widgets.push(widgetData);
  });
  localStorage.setItem('dashboardState', JSON.stringify(widgets));
}

function loadDashboardState() {
  const savedState = localStorage.getItem('dashboardState');
  if (savedState) {
    const widgetsData = JSON.parse(savedState);
    const dashboard = document.getElementById('dashboard');
    dashboard.innerHTML = ''; // Maak het dashboard leeg voordat we laden

    widgetsData.forEach(widgetData => {
      const container = document.createElement('div');
      container.classList.add('widget');
      if (document.getElementById('editToggle')?.checked) container.classList.add('edit-mode'); // Controleer of editToggle bestaat
      container.innerHTML = widgetData.html;
      container.setAttribute('style', widgetData.style);
      container.dataset.widgetName = widgetData.name;
      container.id = widgetData.id; 
      
      dashboard.appendChild(container);
      
      // Wacht tot de container daadwerkelijk in het DOM is voordat we scripts uitvoeren
      // en interacties herstellen.
      // Dit kan helpen met timing issues voor Chart.js
      setTimeout(() => {
        const scriptElement = container.querySelector('script');
        if (scriptElement) {
          // Probeer de unieke canvas ID te vinden binnen de geladen HTML
          const canvasMatch = widgetData.html.match(/id="([a-zA-Z0-9_]+Chart_[0-9]+)"/);
          let canvasIdToUse = null;
          if (canvasMatch && canvasMatch[1]) {
            canvasIdToUse = canvasMatch[1];
          }

          const newScript = document.createElement('script');
          let scriptContent = scriptElement.textContent;

          // Als we een canvasId hebben gevonden, zorg ervoor dat het script die gebruikt.
          // Anders, laat het script zoals het is (voor niet-chart widgets)
          if (canvasIdToUse) {
            scriptContent = scriptContent.replace(/getElementById\('[^']+'\)/g, `getElementById('${canvasIdToUse}')`);
          }
          newScript.textContent = scriptContent;
          
          // Verwijder het oude script element om dubbele ID's of foute context te voorkomen
          scriptElement.remove(); 
          container.appendChild(newScript); 
        }
        setupWidgetInteractions(container);
      }, 0); // setTimeout met 0ms helpt om dit naar het einde van de event loop te pushen
    });
  }
}

// Aanpassen loadWidget om data-widget-name toe te voegen
function loadWidget(name) {
  const baseName = name.replace(/\s+/g, '_'); // vervang spaties voor ID's
  const widgetContainerId = `${baseName}Widget_${Date.now()}`;
  const uniqueCanvasId = `${baseName}Chart_${Date.now()}`;

  fetch(`widgets/${name}.html`)
    .then(response => response.text())
    .then(html => {
      const container = document.createElement('div');
      container.classList.add('widget');
      container.dataset.widgetName = name; 
      container.id = widgetContainerId; 
      if (document.getElementById('editToggle')?.checked) container.classList.add('edit-mode');

      const modifiedHtmlContent = html.replace(/id="\w+Chart"/, `id="${uniqueCanvasId}"`);

      container.innerHTML = `
        <button class="delete-btn" onclick="this.parentElement.remove(); saveDashboardState();">✖</button>
        <div class="resize-handle"></div>
        ${modifiedHtmlContent}
      `;
      document.getElementById('dashboard').appendChild(container);

      const scriptMatch = modifiedHtmlContent.match(/<script>([\s\S]*?)<\/script>/);
      if (scriptMatch && scriptMatch[1]) {
        const scriptContent = scriptMatch[1];
        const newScript = document.createElement('script');
        // Zorg ervoor dat getElementById de *nieuwe* unieke canvas ID gebruikt
        // Alleen vervangen als het een chart widget is (te herkennen aan 'Chart' in uniqueCanvasId)
        if (uniqueCanvasId.includes('Chart')) {
            newScript.textContent = scriptContent.replace(/getElementById\('[^']+'\)/g, `getElementById('${uniqueCanvasId}')`);
        } else {
            newScript.textContent = scriptContent; // Voor niet-chart widgets, laat het zoals het is
        }
        container.appendChild(newScript);
      }
      
      setupWidgetInteractions(container);
      saveDashboardState(); 
    })
    .catch(err => {
      alert(`Fout bij laden van widget: ${name}`);
      console.error(err);
    });
}

// Aanpassen setupWidgetInteractions om staat op te slaan na drag/resize
function setupWidgetInteractions(widget) {
  interact(widget)
    .resizable({
      edges: { right: true, bottom: true },
      listeners: {
        move: function (event) {
          let { x, y } = event.target.dataset;
          x = (parseFloat(x) || 0) + event.deltaRect.left;
          y = (parseFloat(y) || 0) + event.deltaRect.top;

          Object.assign(event.target.style, {
            width: `${event.rect.width}px`,
            height: `${event.rect.height}px`,
            // transform: `translate(${x}px, ${y}px)` // We gebruiken grid, dus transform is niet nodig voor positie
          });

          const canvas = event.target.querySelector('canvas');
          if (canvas) {
            canvas.style.width = '100%';
            canvas.style.height = '100%';
          }
          Object.assign(event.target.dataset, { x, y });
        },
        end: function(event) { // Staat opslaan na resize
            saveDashboardState();
        }
      },
      modifiers: [
        interact.modifiers.restrictSize({
          min: { width: 200, height: 200 }
        })
      ]
    })
    .draggable({
      inertia: true,
      modifiers: [
        interact.modifiers.restrictRect({
          restriction: '#dashboard',
          endOnly: true
        })
      ],
      listeners: {
        start: function (event) {
          draggedWidget = event.target;
          event.target.classList.add('dragging');
        },
        move: function (event) {
          const dropzone = document.elementFromPoint(event.clientX, event.clientY);
          const widgets = Array.from(document.getElementById('dashboard').children);
          
          if (dropzone && dropzone.closest('.widget') && dropzone.closest('.widget') !== draggedWidget) {
            const target = dropzone.closest('.widget');
            const dashboard = document.getElementById('dashboard');
            const draggedIndex = widgets.indexOf(draggedWidget);
            const targetIndex = widgets.indexOf(target);

            if (draggedIndex < targetIndex) {
              dashboard.insertBefore(draggedWidget, target.nextSibling);
            } else {
              dashboard.insertBefore(draggedWidget, target);
            }
          }
        },
        end: function (event) {
          event.target.classList.remove('dragging');
          draggedWidget = null;
          saveDashboardState(); // Sla staat op na slepen
        }
      }
    });
}

// Drag event setup
document.querySelectorAll('.widget-item').forEach(item => {
  item.addEventListener('dragstart', event => {
    if (!editMode) {
      event.preventDefault();
      return;
    }
    event.dataTransfer.setData("text/plain", item.dataset.widget);
  });
});

function toggleEditMode() {
  editMode = document.getElementById('editToggle').checked;
  document.body.classList.toggle('edit-mode', editMode);
  document.querySelectorAll('.widget').forEach(widget => {
    widget.classList.toggle('edit-mode', editMode);
  });
}

// Setup drop zone
const dashboard = document.getElementById('dashboard');
dashboard.addEventListener('dragover', event => event.preventDefault());
dashboard.addEventListener('drop', handleDrop);

// Update DOMContentLoaded om editMode correct te initialiseren
document.addEventListener('DOMContentLoaded', () => {
  const editToggle = document.getElementById('editToggle');
  if(editToggle) {
    editToggle.addEventListener('change', toggleEditMode);
    // Initialiseer editMode gebaseerd op de checkbox staat bij het laden
    editMode = editToggle.checked;
    document.body.classList.toggle('edit-mode', editMode);
  }
  loadDashboardState(); // Laad opgeslagen staat
  // Na het laden van de staat, zorg ervoor dat de .edit-mode class op widgets correct is
  document.querySelectorAll('.widget').forEach(widget => {
    widget.classList.toggle('edit-mode', editMode);
  });
});

// Zorg ervoor dat de delete knop ook de staat opslaat
// Dit is al aangepast in de loadWidget functie: onclick="this.parentElement.remove(); saveDashboardState();"