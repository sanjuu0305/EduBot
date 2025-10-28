import streamlit as st
from openai import OpenAI, RateLimitError, AuthenticationError, APIError
import time

# ------------------ PAGE CONFIG ------------------
st.set_page_config(page_title="EduBot 🤖", page_icon="📚", layout="centered")

st.title("💬 EducationBot")
st.caption("Your personal AI study buddy — ask me anything!")

# ------------------ OPENAI SETUP ------------------
try:
    client = OpenAI(api_key=st.secrets["OPENAI_API_KEY"])  # from Streamlit Cloud secrets
except Exception:
    st.error("❌ Missing API key! Please add it to Streamlit secrets as `OPENAI_API_KEY`.")
    st.stop()

# ------------------ SESSION STATE ------------------
if "messages" not in st.session_state:
    st.session_state["messages"] = [
        {"role": "system", "content": "You are EduBot, a friendly AI tutor for students."}
    ]

# Display chat history
for msg in st.session_state.messages:
    if msg["role"] != "system":
        with st.chat_message(msg["role"]):
            st.markdown(msg["content"])

# ------------------ CHAT INPUT + RESPONSE ------------------
if prompt := st.chat_input("Ask me something..."):
    st.session_state.messages.append({"role": "user", "content": prompt})
    with st.chat_message("user"):
        st.markdown(prompt)

    try:
        with st.chat_message("assistant"):
            stream = client.chat.completions.create(
                model="gpt-4o-mini",  # stable + low-cost
                messages=st.session_state.messages,
                stream=True,
            )
            response = st.write_stream(stream)

        # Save AI response
        st.session_state.messages.append({"role": "assistant", "content": response})

    # ------------------ ERROR HANDLING ------------------
    except RateLimitError:
        st.error("⚠️ Too many requests! Please wait a few seconds and try again.")
        time.sleep(2)

    except AuthenticationError:
        st.error("🚫 Invalid API key! Please check your `OPENAI_API_KEY` in secrets.")

    except APIError as e:
        st.error(f"💥 OpenAI API error: {str(e)}")

    except Exception as e:
        st.error(f"❗ Unexpected error: {str(e)}")