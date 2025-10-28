import streamlit as st
from openai import OpenAI

st.set_page_config(page_title="EduBot 🤖", page_icon="📚", layout="centered")

st.title("💬 EducationBot")
st.caption("Your personal AI study buddy — ask me anything!")

# ✅ Load API key from Streamlit secrets
client = OpenAI(api_key=st.secrets["OPENAI_API_KEY"])

# Initialize chat state
if "messages" not in st.session_state:
    st.session_state["messages"] = [
        {"role": "system", "content": "You are EduBot, a friendly AI tutor for students."}
    ]

# Display previous messages
for msg in st.session_state.messages:
    if msg["role"] != "system":
        with st.chat_message(msg["role"]):
            st.markdown(msg["content"])

# User input + AI response
if prompt := st.chat_input("Ask me something..."):
    st.session_state.messages.append({"role": "user", "content": prompt})
    with st.chat_message("user"):
        st.markdown(prompt)

    with st.chat_message("assistant"):
        stream = client.chat.completions.create(
            model="gpt-4o-mini",  # ✅ use this model instead of gpt-5
            messages=st.session_state.messages,
            stream=True,
        )
        response = st.write_stream(stream)

    st.session_state.messages.append({"role": "assistant", "content": response})