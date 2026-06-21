const express = require('express');
const admin = require('firebase-admin');
const cors = require('cors');

// Initialize Firebase Admin with service account
try {
  const serviceAccount = require('../firebase-adminsdk.json');
  admin.initializeApp({
    credential: admin.cert(serviceAccount)
  });
  console.log("Firebase Admin Initialized Successfully!");
} catch (e) {
  console.error("Failed to initialize Firebase Admin. Please ensure firebase-adminsdk.json exists in htdocs/barbershop_api/");
  console.error(e);
}

const app = express();
app.use(express.json());
app.use(cors());

// Health check endpoint
app.get('/', (req, res) => {
  res.send('FCM Server is running!');
});

// Endpoint to send push notifications
app.post('/send', async (req, res) => {
  const { tokens, title, body, data } = req.body;

  if (!tokens || tokens.length === 0) {
    return res.status(400).json({ error: "Tokens are required" });
  }

  const message = {
    notification: {
      title: title,
      body: body,
    },
    android: {
      notification: {
        sound: 'default'
      }
    },
    apns: {
      payload: {
        aps: {
          sound: 'default'
        }
      }
    },
    data: data || {},
    tokens: tokens,
  };

  try {
    const { getMessaging } = require('firebase-admin/messaging');
    const response = await getMessaging().sendEachForMulticast(message);
    console.log(response.successCount + ' messages were sent successfully');
    res.json({ success: true, response });
  } catch (error) {
    console.error('Error sending message:', error);
    res.status(500).json({ success: false, error: error.message });
  }
});

const PORT = 3000;
app.listen(PORT, () => {
  console.log(`FCM Server listening at http://localhost:${PORT}`);
});
