from pyrogram import Client

api_id = 24195388
api_hash = "8a482ddcf7dcb727c94e6f73e353fba9"

app = Client("my_userbot", api_id=api_id, api_hash=api_hash)
app.start()
print("Login berhasil")
app.stop()
