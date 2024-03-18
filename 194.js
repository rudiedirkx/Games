"use strict";

const VERSION = 12;

// self.addEventListener('push', function(e) {
// 	const {title, ...options} = e.data.json();
// 	e.waitUntil(self.registration.showNotification(title, options));
// });

self.addEventListener('message', async function(e) {
// console.log('message', e);
	if (e.data && e.data.turn === true) {
		const olds = await self.registration.getNotifications();
// console.log(olds);
		olds.forEach(old => old.close());

		e.waitUntil(self.registration.showNotification(`Your turn # ${e.data.game}`, {
			body: "Dude, it's your turn again!",
			icon: '/favicon-hilite.ico',
			// tag: 'turn',
			// renotify: true,
		}));
	}

	if (e.data && e.data.test === true) {
		e.waitUntil(self.registration.showNotification(`TEST message # ${e.data.game}`, {
			body: "Messages seem to work. Sound on?",
			icon: '/favicon-hilite.ico',
			// tag: 'turn',
			// renotify: true,
		}));
	}

	if (e.data && e.data.version === true) {
		const client = await self.clients.get(e.source.id);
		// console.log(client);
		client.postMessage({version: VERSION});
	}
});
