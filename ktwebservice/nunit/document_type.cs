using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentOwnerTest
    	{
		private String 			_session;
		private KnowledgeTreeService 	_kt;
		private int 			_folderId;
		private bool			_verbose;
		private Document		_doc1;

		[SetUp]
		public void SetUp()
		{
			this._kt = new KnowledgeTreeService();
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;

			this._folderId = 1;


			this._doc1 = new Document(1, this._session, this._kt, this._verbose,false);
			this._doc1.createFile(this._folderId);

			this._verbose = true;

		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();

			this._kt.logout(this._session);

		}

		[Test]
		public void ChangeTypeTest()
		{
			// NOTE: Create the following type 'NewType' via the admin pages

			kt_document_detail response = this._kt.change_document_type(this._session, this._doc1.docId, "NewType");
			Assert.AreEqual(0, response.status_code);
			if (0 != response.status_code)
			{
				System.Console.WriteLine("Please check that the document type 'NewType' exists in the database! This test should pass if it exists!");
				System.Console.WriteLine("SQL: insert into document_types_lookup(name) values('NewType');");
			}

			// NOTE: we need to test with an unknown type as well

			response = this._kt.change_document_type(this._session, this._doc1.docId, "UnknownType");
			Assert.IsTrue(0 != response.status_code);

	    	}
	}
}
