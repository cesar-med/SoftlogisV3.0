// renderer.js
export function renderWithTemplate(containerId, templateId, data) {
    const container = document.getElementById(containerId);
    container.innerHTML = "";

    if (!data || data.length === 0) {
        container.innerHTML = "<p>No hay registros que mostrar.</p>";
        return;
    }

    const source = document.getElementById(templateId).innerHTML;
    const template = Handlebars.compile(source);
    container.innerHTML = template({ data });
}
