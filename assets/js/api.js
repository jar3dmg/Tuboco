export async function api(action, payload) {
    const response = await fetch(`api.php?action=${action}`, {
        method: payload ? 'POST' : 'GET',
        headers: payload ? { 'Content-Type': 'application/json' } : {},
        body: payload ? JSON.stringify(payload) : undefined,
    });

    return response.json();
}
